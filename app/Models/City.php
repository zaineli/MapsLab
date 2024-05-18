<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use MongoDB\Laravel\Eloquent\Model;

class City extends Model
{
    protected $connection = "mongodb";

    protected $collection = 'newcities';

    protected $fillable = [
        "locId",
        "country",
        "region",
        "city",
        "latitude",
        "longitude",
        "loc"
    ];
    protected $casts = [
        'longitude' => 'float',
        'latitude' => 'float',
    ];

}
