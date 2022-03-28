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
        $aircraft = Aircraft::where('icao', $aircraftIcao)->first();
        if(!$aircraft) return 1;
        return round(($distance/$aircraft->speed), 2) * 60 * 60;
    }

}
