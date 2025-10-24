<?php

namespace App\Models;

use App\Http\Controllers\AircraftController;
use App\Http\Controllers\AirportController;
use App\Http\Controllers\SlotController;
use Database\Factories\SlotFactory;
use Illuminate\Database\Eloquent\Model;

class Slot extends Model
{
    protected $fillable = [
        'flightNumber',
        'isFixedFlightNumber',
        'origin',
        'isFixedOrigin',
        'destination',
        'isFixedDestination',
        'etaOrigin',
        'isFixedEtaOrigin',
        'eobtOrigin',
        'isFixedeobtOrigin',
        'etaDestination',
        'isFixedEtaDestination',
        'eobtDestination',
        'isFixedeobtDestination',
        'gate',
        'aircraft',
        'isFixedAircraft',
        'route',
        'bookingTime',
        'bookingStatus'
    ];

    protected $hidden = [
        'created_at',
        'updated_at'
    ];

    public static $allowedQueryParams = [
        'flightNumber',
        'isFixedFlightNumber',
        'aircraft',
        'isFixedAircraft',
        'origin',
        'isFixedOrigin',
        'destination',
        'isFixedDestination'
    ];

    protected $casts = [
        'bookingTime' => 'datetime:Y-m-d\TH:i:sP'
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [
        'bookingTime'
    ];

    public function owner()
    {
        return $this->belongsTo(User::class, 'pilotId', 'id');
    }

    public function event()
    {
        return $this->belongsTo(Event::class, 'eventId', 'id');
    }

    public static function _factory()
    {
        return SlotFactory::new();
    }
}
