<?php

namespace App\Http\Controllers;

use App\Services\HQAPIService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

class AirportController extends Controller
{
    const AIRPORT_DETAILS_CACHE_KEY_PREFIX = 'airport_details';
    const ONE_DEGREE_IN_MILE = '60';

    private $hqApi;

    public function __construct(HQAPIService $hqApi)
    {
        $this->hqApi = $hqApi;
    }

    public static function getAirportByICAO(string $icao) {
        $cacheKey = AirportController::AIRPORT_DETAILS_CACHE_KEY_PREFIX . "_$icao";
        $cacheTtl = Carbon::now()->addMonth();

        return Cache::remember($cacheKey, $cacheTtl, function () use ($icao) {
            $hqApi = new HQAPIService;
            return $hqApi->getAirportDataByIcao($icao);
        });
    }

    public static function getCircleDistanceBetweenAirports(string $origin = "", string $destination = "")
    {
        if(!$origin || !$destination)
            return 1;
        $origin = AirportController::getAirportByICAO($origin);
        $destination = AirportController::getAirportByICAO($destination);

        $latDistance = AirportController::getLatDistance($origin['latitude'], $destination['latitude']);
        $lonDistance = AirportController::getLonDistance($origin['longitude'], $destination['longitude']);

        $distance = sqrt( ($latDistance*$latDistance) + ($lonDistance*$lonDistance) );

        $distance = $distance * AirportController::ONE_DEGREE_IN_MILE;

        return round($distance);
    }

    public static function getLatDistance(float $origin, float $destination){
        //If both values are positive or negative (on the same hemisphere)
        if(($origin > 0 && $destination > 0) || ($origin < 0 && $destination < 0))
        {
            return abs(abs($origin) - abs($destination));
        } else {
            //If they are in different hemisphere
            return abs(abs($origin) + abs($destination));
        }
    }

    public static function getLonDistance(float $origin, float $destination){
        //If both values are positive or negative (on the same hemisphere)
        if(($origin > 0 && $destination > 0) || ($origin < 0 && $destination < 0))
        {
            $lonDistance =  abs(abs($origin) - abs($destination));
        } else {
            //If they are in different hemisphere
            $lonDistance =  abs(abs($origin) + abs($destination));
        }

        //If the value is greater than 180 degrees, you must use the other side of the earth
        if($lonDistance > 180){
            $lonDistance = abs($lonDistance - 360);
        }

        return $lonDistance;
    }
}
