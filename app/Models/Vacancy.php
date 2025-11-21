<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Vacancy extends Model
{
    use HasFactory;
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
