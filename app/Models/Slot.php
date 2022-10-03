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
        'origin',
        'destination',
        'type',
        'slotTime',
        'gate',
        'aircraft',
        'route',
        'bookingTime',
        'bookingStatus',
        'private'
    ];

    protected $hidden = [
        'created_at',
        'updated_at'
    ];

    public static $allowedQueryParams = [
        'flightNumber',
        'aircraft',
        'type',
        'private',
        'origin',
        'destination'
    ];

    protected $appends = [
        'flight_time',
        'distance',
        'timestamps'
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

    public function getFlightTimeAttribute()
    {
        if(!$this->aircraft) return 1;
        return AircraftController::getFlightTimeFromICAO($this->aircraft, $this->getDistanceAttribute());
    }

    public function getDistanceAttribute()
    {
        if(!$this->origin || !$this->destination) return 1;
        return AirportController::getCircleDistanceBetweenAirports($this->origin, $this->destination);
    }

    public function getTimestampsAttribute()
    {
        if(!$this->origin || !$this->destination) return [1,1];
        return SlotController::getSlotTimestamps($this);
    }

    public function getTimestamps()
    {
        if(!$this->origin || !$this->destination) return [1,1];
        return SlotController::getSlotTimestamps($this);
    }

    public function aircraftData()
    {
        return $this->hasOne(Aircraft::class, 'icao', 'aircraft');
    }
}
