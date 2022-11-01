<?php

namespace App\Models;

use Carbon\Carbon;
use Database\Factories\EventFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Event
 *
 * @property int $id
 * @property string $division
 * @property \Illuminate\Support\Carbon $dateStart
 * @property \Illuminate\Support\Carbon $dateEnd
 * @property string $eventName
 * @property int $privateSlots
 * @property string $status
 * @property int $createdBy
 * @property string $description
 * @property string $banner
 * @property string $atcBooking
 * @property string $atcBriefing
 * @property string $pilotBriefing
 * @property int $public
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $type
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\EventAirport[] $airports
 * @property-read int|null $airports_count
 * @property-read \App\Models\User $creator
 * @property-read bool $can_auto_book
 * @property-read mixed $can_confirm_slots
 * @property-read mixed $has_ended
 * @property-read mixed $has_started
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Scenery[] $sceneries
 * @property-read int|null $sceneries_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Slot[] $slots
 * @property-read int|null $slots_count
 * @method static \Illuminate\Database\Eloquent\Builder|Event newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Event newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Event query()
 * @method static \Illuminate\Database\Eloquent\Builder|Event whereAtcBooking($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Event whereAtcBriefing($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Event whereBanner($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Event whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Event whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Event whereDateEnd($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Event whereDateStart($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Event whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Event whereDivision($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Event whereEventName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Event whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Event wherePilotBriefing($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Event wherePrivateSlots($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Event wherePublic($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Event whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Event whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Event whereUpdatedAt($value)
 * @mixin \Eloquent
 * @property bool $allowBookingAfterStart
 * @method static \Illuminate\Database\Eloquent\Builder|Event whereAllowBookingAfterStart($value)
 */
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
        'type',
        'allowBookingAfterStart'
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

    public function getHasStartedAttribute(): bool
    {
        return $this->dateStart->isPast();
    }

    public function getHasEndedAttribute(): bool
    {
        return $this->dateEnd->isPast();
    }

    public function getCanConfirmSlotsAttribute(): bool
    {
        if ($this->has_ended) {
            return false;
        }

        if ($this->has_started && $this->allowBookingAfterStart) {
            return true;
        }

        if ($this->has_started) {
            return false;
        }

        $diffInDays = $this->getEventStartRemaningDays();
        $flightConfirmMaxDaysBefore = $this->getFlightConfirmMaxDaysBefore();
        return $flightConfirmMaxDaysBefore >= $diffInDays;
    }

    public function getCanAutoBookAttribute(): bool
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
