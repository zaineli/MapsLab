<?php

use App\Http\Controllers\CityController;
use Illuminate\Support\Facades\Route;

Route::get('/',[CityController::class, 'index']);

Route::post('/cities/{latitude},{longitude}/get-city', [CityController::class, 'getCity']);



Route::post('/get-cities', [CityController::class, 'get_cities'])->name('get-cities');

Route::post('/get-cities-from-map', [CityController::class, 'get_cities_from_map'])->name('get-cities-from-map');

Route::post('/get-cities-through-select', [CityController::class, 'get_cities_through_select'])->name('get-cities-from-map');

Route::post('/cities/{latitude},{longitude}/get-city', [CityController::class, 'getCity']);