<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\GeocodingService;
use App\Models\UserQuery;

class AddressController extends Controller
{
    private $geocodingService;

    public function __construct(GeocodingService $geocodingService)
    {
        $this->geocodingService = $geocodingService;
    }

    public function index()
    {
        return view('address');
    }

    public function search(Request $request)
    {
        $address = $request->input('address');
        $results = [];

        if ($address) {
            // Сохраняем уникальный запрос
            if (!UserQuery::where('query', $address)->exists()) {
                UserQuery::create(['query' => $address]);
            }

            // Получаем результаты геокодирования
            $results = $this->geocodingService->geocodeAddress($address);
        }

        return view('address', [
            'results' => $results,
            'searchQuery' => $address
        ]);
    }
}
