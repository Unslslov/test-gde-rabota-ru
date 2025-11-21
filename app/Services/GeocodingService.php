<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeocodingService
{
    private $apiKey;
    private $baseUrl = 'https://geocode-maps.yandex.ru/1.x/';

    public function __construct()
    {
        $this->apiKey = env('YANDEX_GEOCODER_API_KEY');
    }

    public function geocodeAddress(string $address): array
    {
        try {
            $response = Http::get($this->baseUrl, [
                'apikey' => $this->apiKey,
                'geocode' => $address,
                'format' => 'json',
                'results' => 5,
                'lang' => 'ru_RU'
            ]);

            if ($response->successful()) {
                return $this->parseResponse($response->json());
            }

            Log::warning('Geocoding API error: ' . $response->status());
            return [];
        } catch (\Exception $e) {
            Log::error('Geocoding error: ' . $e->getMessage());
            return [];
        }
    }

    private function parseResponse(array $data): array
    {
        $results = [];

        if (!isset($data['response']['GeoObjectCollection']['featureMember'])) {
            Log::warning('No featureMember in API response');
            return [];
        }

        foreach ($data['response']['GeoObjectCollection']['featureMember'] as $item) {
            $geoObject = $item['GeoObject'];
            $metaData = $geoObject['metaDataProperty']['GeocoderMetaData'];

            $result = $this->parseGeoObject($geoObject, $metaData);

            if ($this->isMoscowAddress($result)) {
                $results[] = $result;
            }
        }

        return $results;
    }

    private function parseGeoObject(array $geoObject, array $metaData): array
    {
        $addressComponents = $metaData['Address']['Components'] ?? [];
        $extendedDetails = $this->getExtendedDetails($metaData);
        $coordinates = $this->getCoordinates($geoObject);

        // Используем двухэтапный поиск района
        $district = $this->findDistrict($addressComponents, $extendedDetails);
        if ($district === 'Не указан' && !empty($coordinates['longitude']) && !empty($coordinates['latitude'])) {
            $district = $this->findDistrictByCoordinates(
                (float)$coordinates['longitude'],
                (float)$coordinates['latitude']
            );
        }

        // Используем двухэтапный поиск метро
        $metro = $this->findMetroStations($metaData);
        if ($metro === 'Не указано' && !empty($coordinates['longitude']) && !empty($coordinates['latitude'])) {
            $metro = $this->findMetroByCoordinates(
                (float)$coordinates['longitude'],
                (float)$coordinates['latitude']
            );
        }

        // Используем двухэтапный поиск улицы
        $street = $this->findStreet($addressComponents, $extendedDetails);
        if ($street === 'Не указана' && !empty($coordinates['longitude']) && !empty($coordinates['latitude'])) {
            $street = $this->findStreetByCoordinates(
                (float)$coordinates['longitude'],
                (float)$coordinates['latitude']
            );
        }

        return [
            'address' => $geoObject['name'],
            'full_address' => $metaData['text'] ?? $geoObject['name'],
            'district' => $district,
            'metro' => $metro,
            'street' => $street,
            'house' => $this->findHouse($addressComponents, $extendedDetails),
            'type' => $metaData['kind'] ?? 'unknown',
            'coordinates' => $coordinates
        ];
    }

    private function findDistrictByCoordinates(float $longitude, float $latitude): string
    {
        try {
            $response = Http::get($this->baseUrl, [
                'apikey' => $this->apiKey,
                'geocode' => "$longitude,$latitude",
                'kind' => 'district',
                'format' => 'json',
                'results' => 1,
                'lang' => 'ru_RU'
            ]);

            if ($response->successful()) {
                $data = $response->json();

                if (isset($data['response']['GeoObjectCollection']['featureMember'][0])) {
                    $geoObject = $data['response']['GeoObjectCollection']['featureMember'][0]['GeoObject'];
                    $districtName = $geoObject['name'] ?? '';

                    if (!empty($districtName) &&
                        !in_array($districtName, ['Москва', 'Центральный федеральный округ', 'Россия'])) {
                        return $districtName;
                    }
                }
            }
        } catch (\Exception $e) {
            Log::error('District search by coordinates error: ' . $e->getMessage());
        }

        return 'Не указан';
    }

    private function findMetroByCoordinates(float $longitude, float $latitude): string
    {
        try {
            $response = Http::get($this->baseUrl, [
                'apikey' => $this->apiKey,
                'geocode' => "$longitude,$latitude",
                'kind' => 'metro',
                'format' => 'json',
                'results' => 3,
                'lang' => 'ru_RU'
            ]);

            if ($response->successful()) {
                $data = $response->json();
                $metroStations = [];

                if (isset($data['response']['GeoObjectCollection']['featureMember'])) {
                    foreach ($data['response']['GeoObjectCollection']['featureMember'] as $item) {
                        $geoObject = $item['GeoObject'];
                        $metroName = $geoObject['name'] ?? '';

                        $metroName = str_replace('метро ', '', $metroName);

                        if (!empty($metroName)) {
                            $metroStations[] = $metroName;
                        }
                    }
                }

                if (!empty($metroStations)) {
                    return implode(', ', array_slice($metroStations, 0, 3));
                }
            }
        } catch (\Exception $e) {
            Log::error('Metro search by coordinates error: ' . $e->getMessage());
        }

        return 'Не указано';
    }

    private function findStreetByCoordinates(float $longitude, float $latitude): string
    {
        try {
            $response = Http::get($this->baseUrl, [
                'apikey' => $this->apiKey,
                'geocode' => "$longitude,$latitude",
                'kind' => 'street', // Ищем улицы
                'format' => 'json',
                'results' => 1,
                'lang' => 'ru_RU'
            ]);

            if ($response->successful()) {
                $data = $response->json();

                if (isset($data['response']['GeoObjectCollection']['featureMember'][0])) {
                    $geoObject = $data['response']['GeoObjectCollection']['featureMember'][0]['GeoObject'];
                    $streetName = $geoObject['name'] ?? '';

                    // Очищаем название от ненужных дополнений
                    if (!empty($streetName)) {
                        // Убираем "улица" из названия если есть
                        $streetName = str_replace(['улица ', 'ул. '], '', $streetName);
                        return $streetName;
                    }
                }
            }
        } catch (\Exception $e) {
            Log::error('Street search by coordinates error: ' . $e->getMessage());
        }

        return 'Не указана';
    }

    private function findDistrict(array $components, array $extendedDetails): string
    {
        $districtSources = [
            $this->findComponent($components, 'district'),
            $this->findComponent($components, 'area'),
            $extendedDetails['district'] ?? '',
            $this->findComponent($components, 'suburb')
        ];

        foreach ($districtSources as $district) {
            if (!empty($district) && $district !== 'Москва') {
                return $district;
            }
        }

        return 'Не указан';
    }

    private function findMetroStations(array $metaData): string
    {
        $metroStations = [];

        if (isset($metaData['Address']['Components'])) {
            foreach ($metaData['Address']['Components'] as $component) {
                if ($component['kind'] === 'metro') {
                    $name = str_replace('метро ', '', $component['name']);
                    $metroStations[] = $name;
                }
            }
        }

        if (isset($metaData['AddressDetails']['Country']['AdministrativeArea']['Locality']['DependentLocality']['Thoroughfare']['Premise']['MetroStation'])) {
            $metro = $metaData['AddressDetails']['Country']['AdministrativeArea']['Locality']['DependentLocality']['Thoroughfare']['Premise']['MetroStation'];
            $metroStations[] = $metro['MetroStationName'] ?? '';
        }

        return empty($metroStations) ? 'Не указано' : implode(', ', array_filter($metroStations));
    }

    private function findStreet(array $components, array $extendedDetails): string
    {
        $streetSources = [
            $this->findComponent($components, 'street'),
            $this->findComponent($components, 'route'),
            $extendedDetails['street'] ?? '',
            $this->findComponent($components, 'thoroughfare'),
            $this->extractStreetFromAddress($components) // Новый метод для извлечения из полного адреса
        ];

        foreach ($streetSources as $street) {
            if (!empty($street) && $street !== 'Москва') {
                return $street;
            }
        }

        return 'Не указана';
    }

    private function extractStreetFromAddress(array $components): string
    {
        // Пытаемся извлечь улицу из полного адреса
        foreach ($components as $component) {
            if (in_array($component['kind'], ['street', 'route', 'thoroughfare'])) {
                return $component['name'];
            }
        }

        // Если не нашли в компонентах, ищем в других полях
        return '';
    }

    private function findHouse(array $components, array $extendedDetails): string
    {
        $houseSources = [
            $this->findComponent($components, 'house'),
            $extendedDetails['house'] ?? '',
            $this->findComponent($components, 'premise'),
            $this->extractHouseFromAddress($components) // Новый метод для дома
        ];

        foreach ($houseSources as $house) {
            if (!empty($house)) {
                return $house;
            }
        }

        return 'Не указан';
    }

    private function extractHouseFromAddress(array $components): string
    {
        foreach ($components as $component) {
            if ($component['kind'] === 'house') {
                return $component['name'];
            }
        }
        return '';
    }

    private function getExtendedDetails(array $metaData): array
    {
        $details = [];

        if (isset($metaData['AddressDetails']['Country'])) {
            $country = $metaData['AddressDetails']['Country'];

            if (isset($country['AdministrativeArea'])) {
                $adminArea = $country['AdministrativeArea'];

                if (isset($adminArea['Locality'])) {
                    $locality = $adminArea['Locality'];

                    if (isset($locality['DependentLocality'])) {
                        $dependentLocality = $locality['DependentLocality'];
                        $details['district'] = $dependentLocality['DependentLocalityName'] ?? '';

                        if (isset($dependentLocality['Thoroughfare'])) {
                            $thoroughfare = $dependentLocality['Thoroughfare'];
                            $details['street'] = $thoroughfare['ThoroughfareName'] ?? '';

                            if (isset($thoroughfare['Premise'])) {
                                $details['house'] = $thoroughfare['Premise']['PremiseNumber'] ?? '';
                            }
                        }
                    } elseif (isset($locality['Thoroughfare'])) {
                        $thoroughfare = $locality['Thoroughfare'];
                        $details['street'] = $thoroughfare['ThoroughfareName'] ?? '';

                        if (isset($thoroughfare['Premise'])) {
                            $details['house'] = $thoroughfare['Premise']['PremiseNumber'] ?? '';
                        }
                    }

                    if (empty($details['district']) && isset($locality['DependentLocality'])) {
                        $dependentLocality = $locality['DependentLocality'];
                        if (isset($dependentLocality['DependentLocalityName'])) {
                            $details['district'] = $dependentLocality['DependentLocalityName'];
                        }
                    }
                }
            }
        }

        return $details;
    }

    private function findComponent(array $components, string $kind): string
    {
        foreach ($components as $component) {
            if (($component['kind'] ?? '') === $kind) {
                return $component['name'];
            }
        }
        return '';
    }

    private function getCoordinates(array $geoObject): array
    {
        if (isset($geoObject['Point']['pos'])) {
            $coordinates = explode(' ', $geoObject['Point']['pos']);
            if (count($coordinates) === 2) {
                return [
                    'longitude' => $coordinates[0],
                    'latitude' => $coordinates[1]
                ];
            }
        }
        return ['longitude' => '', 'latitude' => ''];
    }

    private function isMoscowAddress(array $result): bool
    {
        $fullAddress = $result['full_address'] ?? '';
        $address = $result['address'] ?? '';

        $moscowPatterns = [
            'Москва',
            'г. Москва',
            'город Москва'
        ];

        foreach ($moscowPatterns as $pattern) {
            if (stripos($fullAddress, $pattern) !== false ||
                stripos($address, $pattern) !== false) {
                return true;
            }
        }

        return false;
    }
}
