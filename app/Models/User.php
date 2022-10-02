<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\User
 *
 * @property int $id
 * @property string $vid
 * @property string $firstName
 * @property string $lastName
 * @property int $atcRating
 * @property int $pilotRating
 * @property string $division
 * @property string $country
 * @property int $admin
 * @property int $suspended
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Event[] $eventsCreated
 * @property-read int|null $events_created_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Slot[] $slotsBooked
 * @property-read int|null $slots_booked_count
 * @method static \Illuminate\Database\Eloquent\Builder|User newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|User newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|User query()
 * @method static \Illuminate\Database\Eloquent\Builder|User whereAdmin($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereAtcRating($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereCountry($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereDivision($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereFirstName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereLastName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User wherePilotRating($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereSuspended($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereVid($value)
 * @mixin \Eloquent
 */
class User extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'vid', 'firstName', 'lastName', 'atcRating', 'pilotRating', 'email', 'division', 'country'
    ];

    protected $hidden = [
        'created_at', 'updated_at'
    ];

    public function eventsCreated()
    {
        return $this->hasMany(Event::class);
    }

    public function slotsBooked()
    {
        return $this->hasMany(Slot::class, 'pilotId', 'id');
    }

    public static function _factory()
    {
        return UserFactory::new();
    }
}
