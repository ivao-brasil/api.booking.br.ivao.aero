<?php

namespace App\Models;

use Carbon\Carbon;
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

        $diffInDays = $this->getEventStartRemaningDays();
        $flightConfirmMaxDaysBefore = $this->getFlightConfirmMaxDaysBefore();
        return $flightConfirmMaxDaysBefore >= $diffInDays;
    }

    public function getCanAutoBookAttribute()
    {
        if (!$this->can_confirm_slots) {
            return false;
        }

        $diffInDays = $this->getEventStartRemaningDays();
        $ignoreConfirmationHours = $this->getIgnoreSlotConfirmationDays();

        if (!$ignoreConfirmationHours) {
            return false;
        }

        return $ignoreConfirmationHours >= $diffInDays;
    }

    public function getFlightConfirmMaxDaysBefore(): int
    {
        return config('app.slot.before_event_to_confirm_days') ?: 7;
    }

    public function getIgnoreSlotConfirmationDays(): ?int
    {
        return config('app.slot.ignore_slot_confirmation_days');
    }

    private function getEventStartRemaningDays(): int
    {
        $today = Carbon::now();
        /** @var \Carbon\Carbon */
        $startDate = $this->dateStart;
        $diffInDays = $today->diffInDays($startDate);

        return $diffInDays;
    }
}
