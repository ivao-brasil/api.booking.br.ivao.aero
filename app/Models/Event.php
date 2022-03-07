<?php

namespace App\Models;

use Database\Factories\EventFactory;
use Illuminate\Database\Eloquent\Model;

class Event extends Model
{

    protected $fillable = [
        'division',
        'dateStart',
        'dateEnd',
        'eventName',
        'privateSlots',
        'status',
        'createdBy',
        'pilotBriefing',
        'atcBriefing',
        'description',
        'atcBooking',
        'banner',
    ];

    protected $casts = [
        'dateStart' => 'datetime:Y-m-d\TH:i:sP',
        'dateEnd' => 'datetime:Y-m-d\TH:i:sP'
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [
        'dateStart',
        'dateEnd'
    ];

    protected $appends = ['has_started', 'has_ended'];

    public function creator()
    {
        return $this->belongsTo(User::class, 'createdBy', 'id');
    }

    public function slots()
    {
        return $this->hasMany(Slot::class, 'eventId', 'id');
    }

    public function sceneries()
    {
        return $this->hasMany(Scenery::class, 'eventId', 'id');
    }

    public static function _factory()
    {
        return EventFactory::new();
    }

    public function airports()
    {
        return $this->hasMany(EventAirport::class, 'eventId', 'id');
    }

    public function getHasStartedAttribute()
    {
        if(strtotime($this->dateStart) < time()) return true;
        return false;
    }

    public function getHasEndedAttribute()
    {
        if(strtotime($this->dateEnd) < time()) return true;
        return false;
    }
}
