<?php

namespace Database\Seeders;

use App\Models\Vacancy;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class VacanciesTableSeeder extends Seeder
{
    public function run()
    {
        Vacancy::factory(10)->create();
    }
}
