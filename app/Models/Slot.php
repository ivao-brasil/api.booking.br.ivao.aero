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

    protected $appends = ['flight_time', 'distance', 'timestamps'];

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
