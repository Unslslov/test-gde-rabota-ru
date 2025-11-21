<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class VacanciesTableSeeder extends Seeder
{
    public function run()
    {
        $vacancies = [
            [
                'title' => 'PHP разработчик',
                'salary_from' => 80000,
                'salary_to' => 120000,
                'country_id' => 1,
                'region_id' => 1,
                'city_id' => 1,
                'created_at' => now(),
            ],
            [
                'title' => 'Senior JavaScript разработчик',
                'salary_from' => 150000,
                'salary_to' => 250000,
                'country_id' => 1,
                'region_id' => 1,
                'city_id' => 1,
                'created_at' => now(),
            ],
        ];

        DB::table('vacancies')->insert($vacancies);
    }
}
