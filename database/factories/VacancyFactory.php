<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Vacancy>
 */
class VacancyFactory extends Factory
{
    const TITLES = [
        'Разработчик PHP',
        'Менеджер по продажам',
        'Маркетолог',
        'Дизайнер UI/UX',
        'Аналитик данных',
        'Системный администратор',
        'Бухгалтер',
        'Менеджер проекта',
        'Тестировщик ПО',
        'Специалист поддержки'
    ];

    public function definition(): array
    {
        return [
            'title' => self::TITLES[array_rand(self::TITLES)],
            'salary_from' => rand(50000, 70000),
            'salary_to' => rand(70000, 120000),
            'country_id' => 1,
            'region_id' => 1,
            'city_id' => 1,
        ];
    }
}
