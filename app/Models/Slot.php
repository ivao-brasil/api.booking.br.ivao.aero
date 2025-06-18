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
 * @property \Illuminate\Support\Carbon $etibOrigin
 * @property bool isFixedEtibOrigin
 * @property \Illuminate\Support\Carbon $etobOrigin
 * @property bool isFixedEtobOrigin
 * @property \Illuminate\Support\Carbon $etibDestination
 * @property bool isFixedEtibDestination
 * @property \Illuminate\Support\Carbon $etobDestination
 * @property bool isFixedEtobDestination
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
        'etibOrigin',
        'isFixedEtibOrigin',
        'etobOrigin',
        'isFixedEtobOrigin',
        'etibDestination',
        'isFixedEtibDestination',
        'etobDestination',
        'isFixedEtobDestination',
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
}
