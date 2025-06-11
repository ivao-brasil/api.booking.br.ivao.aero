<?php

namespace App\Models;

use App\Http\Controllers\AircraftController;
use App\Http\Controllers\AirportController;
use App\Http\Controllers\SlotController;
use Database\Factories\SlotFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Slot
 *
 * @property int $id
 * @property string|null $flightNumber
 * @property bool $isFixedFlightNumber
 * @property string|null $origin
 * @property bool $isFixedOrigin
 * @property string|null $destination
 * @property bool $isFixedDestination
 * @property string $type
 * @property string $slotTime
 * @property string|null $gate
 * @property string|null $aircraft
 * @property bool $isFixedAircraft
 * @property int|null $pilotId
 * @property int $eventId
 * @property \Illuminate\Support\Carbon|null $bookingTime
 * @property string $bookingStatus
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Aircraft|null $aircraftData
 * @property-read \App\Models\Event $event
 * @property-read mixed $distance
 * @property-read mixed $flight_time
 * @property-read mixed $timestamps
 * @property-read \App\Models\User|null $owner
 * @method static \Illuminate\Database\Eloquent\Builder|Slot newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Slot newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Slot query()
 * @method static \Illuminate\Database\Eloquent\Builder|Slot whereAircraft($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Slot whereBookingStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Slot whereBookingTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Slot whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Slot whereDestination($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Slot whereEventId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Slot whereFlightNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Slot whereGate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Slot whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Slot whereOrigin($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Slot wherePilotId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Slot whereSlotTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Slot whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Slot whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Slot extends Model
{
    protected $fillable = [
        'flightNumber',
        'isFixedFlightNumber',
        'origin',
        'isFixedOrigin',
        'destination',
        'isFixedDestination',
        'type',
        'slotTime',
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
        'type',
        'origin',
        'isFixedOrigin',
        'destination',
        'isFixedDestination'
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
