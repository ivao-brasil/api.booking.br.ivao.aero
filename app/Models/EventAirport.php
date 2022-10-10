<?php

namespace App\Models;

use Database\Factories\EventAirportFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\EventAirport
 *
 * @property int $id
 * @property int $eventId
 * @property string $icao
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Event|null $airports
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Scenery[] $sceneries
 * @property-read int|null $sceneries_count
 * @method static \Illuminate\Database\Eloquent\Builder|EventAirport newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|EventAirport newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|EventAirport query()
 * @method static \Illuminate\Database\Eloquent\Builder|EventAirport whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EventAirport whereEventId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EventAirport whereIcao($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EventAirport whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|EventAirport whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class EventAirport extends Model
{
    protected $fillable = [
        'eventId',
        'icao',
    ];

    //TODO: FIX THIS F-ING MESS
    protected $table = 'event_airports';

    public function airports()
    {
        return $this->hasOne(Event::class, 'id', 'eventId');
    }

    public function sceneries()
    {
        return $this->hasMany(Scenery::class, 'icao', 'icao');
    }

    public static function _factory()
    {
        return EventAirportFactory::new();
    }
}
