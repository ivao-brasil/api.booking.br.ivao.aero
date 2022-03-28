<?php

namespace App\Http\Controllers;

use App\Models\Aircraft;
use App\Services\HQAPIService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

class AircraftController extends Controller
{
    //Returns the flight time in seconds
    public static function getFlightTimeFromICAO(string $aircraftIcao, float $distance)
    {
        $speed = Aircraft::where('icao', $aircraftIcao)->first()->speed;
        return round(($distance/$speed), 2) * 60 * 60;
    }

}
