<?php

namespace App\Http\Controllers;

use App\Models\City;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CityController extends Controller
{
    public function index()
    {
        return view('home', ['all_cities' => City::where('city', '!=', '')->limit(500)->get()]);
    }

    public function getCity()
    {
        $city = City::find(request('city'));
        
        $surrounding_cities = City::where('lo', '$near', [
            '$geometry' => [
                'type' => 'Point',
                'coordinates' => $city->loc['coordinates']
            ],
            '$maxDistance' => 10000
        ])->get();
        return response()->json(['city' => $city]);
    }
    function get_cities_through_select()
    {
        if (request('city') === null) {
            return response()->json(['status' => false, 'city' => null, 'Latitude' => null, 'Longitude' => null, 'cities' => null]);
        }

        $city = City::where('city', 'LIKE', '%' . request('city') . '%')->first();

        $latitude = $city->latitude;
        $longitude = $city->longitude;

        $range = 2.5;

        $cities = City::whereBetween('latitude', [$latitude - $range, $latitude + $range])->whereBetween('longitude', [$longitude - $range, $longitude + $range])->get();

        $city_dist_array = array();

        for ($i = 0; $i < count($cities); $i++) {

            $lat1 = $latitude * M_PI / 180;
            $lat2 = $cities[$i]->latitude * M_PI / 180;

            $lon1 = $longitude * M_PI / 180;
            $lon2 = $cities[$i]->longitude * M_PI / 180;

            $delta_lat = ($cities[$i]->latitude - $latitude) * M_PI / 180;
            $delta_lon = ($cities[$i]->longitude - $longitude) * M_PI / 180;

            $a = sin($delta_lat / 2) * sin($delta_lat / 2) + cos($lat1) * cos($lat2) * sin($delta_lon / 2) * sin($delta_lon / 2);
            $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

            $d = (6371e3 * $c) / 1000.0;

            $city_dist_array[$i] = [
                'city' => $cities[$i],
                'distance' => $d
            ];
        }

        $city_dist_array = collect($city_dist_array)->sortBy('distance')->values()->all();

        $city_dist_array = array_slice($city_dist_array, 0, 6);

        if ($city === null) {
            $success = false;
            $city = null;
        } else {
            $success = true;
        }

        return response()->json(['status' => $success, 'city' => $city, 'Latitude' => $latitude, 'Longitude' => $longitude, 'cities' => $city_dist_array]);
    }

    function get_cities()
    {
        if (request('city') === null) {
            return response()->json(['status' => false, 'city' => null, 'Latitude' => null, 'Longitude' => null, 'cities' => null]);
        }

        $city = City::where('city', 'LIKE', '%' . request('city') . '%')->first();

        $latitude = $city->latitude;
        $longitude = $city->longitude;

        $range = 2.5;

        $cities = City::whereBetween('latitude', [$latitude - $range, $latitude + $range])->whereBetween('longitude', [$longitude - $range, $longitude + $range])->get();

        $city_dist_array = array();

        for ($i = 0; $i < count($cities); $i++) {

            $lat1 = $latitude * M_PI / 180;
            $lat2 = $cities[$i]->latitude * M_PI / 180;

            $lon1 = $longitude * M_PI / 180;
            $lon2 = $cities[$i]->longitude * M_PI / 180;

            $delta_lat = ($cities[$i]->latitude - $latitude) * M_PI / 180;
            $delta_lon = ($cities[$i]->longitude - $longitude) * M_PI / 180;

            $a = sin($delta_lat / 2) * sin($delta_lat / 2) + cos($lat1) * cos($lat2) * sin($delta_lon / 2) * sin($delta_lon / 2);
            $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

            $d = (6371e3 * $c) / 1000.0;

            $city_dist_array[$i] = [
                'city' => $cities[$i],
                'distance' => $d
            ];
        }

        $city_dist_array = collect($city_dist_array)->sortBy('distance')->values()->all();

        $city_dist_array = array_slice($city_dist_array, 0, 6);

        if ($city === null) {
            $success = false;
            $city = null;
        } else {
            $success = true;
        }

        return response()->json(['status' => $success, 'city' => $city, 'Latitude' => $latitude, 'Longitude' => $longitude, 'cities' => $city_dist_array]);
    }

    function get_cities_from_map()
    {
        $latitude = request('lat');
        $longitude = request('lon');

        $tolerance = 2;

        $cities = City::whereBetween('latitude', [$latitude - $tolerance, $latitude + $tolerance])->whereBetween('longitude', [$longitude - $tolerance, $longitude + $tolerance])->get();

        $closest_city = null;

        $min_distance = 1000000000;

        for ($i = 0; $i < count($cities); $i++) {

            $lat1 = $latitude * M_PI / 180;
            $lat2 = $cities[$i]->latitude * M_PI / 180;

            $lon1 = $longitude * M_PI / 180;
            $lon2 = $cities[$i]->longitude * M_PI / 180;

            $delta_lat = ($cities[$i]->latitude - $latitude) * M_PI / 180;
            $delta_lon = ($cities[$i]->longitude - $longitude) * M_PI / 180;

            $a = sin($delta_lat / 2) * sin($delta_lat / 2) + cos($lat1) * cos($lat2) * sin($delta_lon / 2) * sin($delta_lon / 2);
            $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

            $d = (6371e3 * $c) / 1000.0;

            if ($d < $min_distance) {
                $min_distance = $d;
                $closest_city = $cities[$i];
            }
        }

        $city = $closest_city;

        $range = 2.5;

        $cities = City::whereBetween('latitude', [$latitude - $range, $latitude + $range])->whereBetween('longitude', [$longitude - $range, $longitude + $range])->get();

        $city_dist_array = array();

        for ($i = 0; $i < count($cities); $i++) {

            $lat1 = $latitude * M_PI / 180;
            $lat2 = $cities[$i]->latitude * M_PI / 180;

            $lon1 = $longitude * M_PI / 180;
            $lon2 = $cities[$i]->longitude * M_PI / 180;

            $delta_lat = ($cities[$i]->latitude - $latitude) * M_PI / 180;
            $delta_lon = ($cities[$i]->longitude - $longitude) * M_PI / 180;

            $a = sin($delta_lat / 2) * sin($delta_lat / 2) + cos($lat1) * cos($lat2) * sin($delta_lon / 2) * sin($delta_lon / 2);
            $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

            $d = (6371e3 * $c) / 1000.0;

            $city_dist_array[$i] = [
                'city' => $cities[$i],
                'distance' => $d
            ];
        }

        $city_dist_array = collect($city_dist_array)->sortBy('distance')->values()->all();

        $city_dist_array = array_slice($city_dist_array, 0, 6);

        if ($city === null) {
            $success = false;
            $city = null;
        } else {
            $success = true;
        }

        return response()->json(['status' => $success, 'city' => $city, 'Latitude' => $latitude, 'Longitude' => $longitude, 'cities' => $city_dist_array]);
    }
    
}
