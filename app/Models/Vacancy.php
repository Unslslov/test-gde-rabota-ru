<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Vacancy extends Model
{
    protected $fillable = [
        'title',
        'salary_from',
        'salary_to',
        'country_id',
        'region_id',
        'city_id',
        'created_at'
    ];

    public $timestamps = false;
}
