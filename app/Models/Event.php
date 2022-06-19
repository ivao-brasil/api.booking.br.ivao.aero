<?php

namespace App\Models;

use Carbon\Carbon;
use Database\Factories\EventFactory;
use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    public const FLIGHT_CONFIRM_MAX_DAYS_BEFORE = 7;

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
        'type'
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

    protected $appends = [
        'has_started',
        'has_ended',
        'can_confirm_slots'
    ];

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
        return $this->dateStart->isPast();
    }

    public function getHasEndedAttribute()
    {
        return $this->dateEnd->isPast();
    }

    public function getCanConfirmSlotsAttribute()
    {
        if ($this->has_started) {
            return false;
        }

        $today = Carbon::now();
        $startDate = $this->dateStart;
        $diffInDays = $today->diffInDays($startDate);

        return Event::FLIGHT_CONFIRM_MAX_DAYS_BEFORE >= $diffInDays;
    }

    public function getFlightConfirmMaxDaysBefore()
    {
        return config('app.slot.days_before_event_to_confirm') ?: Event::FLIGHT_CONFIRM_MAX_DAYS_BEFORE;
    }
}
