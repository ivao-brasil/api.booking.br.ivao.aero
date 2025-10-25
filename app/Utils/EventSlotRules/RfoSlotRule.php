<?php
namespace App\Utils\EventSlotRules;

use App\Models\EventAirport;
use App\Models\Slot;
use Illuminate\Database\Eloquent\Builder;

class RfoSlotRule implements SlotRuleInterface
{
    public function applyListTypeFilter(string $eventId, Builder $query, string $value): Builder
    {
        if ($value == "takeoff") {
            $airportIcao = EventAirport::where('eventId', $eventId)->pluck('icao')->toArray();
            return $query->whereIn('origin', $airportIcao)
                ->whereNotIn('destination', $airportIcao)
                ->where('isPrivate', 0);
        } elseif ($value == "landing") {
            $airportIcao = EventAirport::where('eventId', $eventId)->pluck('icao')->toArray();
            return $query->whereIn('destination', $airportIcao)
                ->whereNotIn('origin', $airportIcao)
                ->where('isPrivate', 0);
        } elseif ($value == "private_takeoff") {
            return $query->where('isFixedOrigin', 1)
                ->where('isFixedDestination', 0)
                ->where('isPrivate', 1);
        } elseif ($value == "private_landing") {
            return $query->where('isFixedOrigin', 0)
                ->where('isFixedDestination', 1)
                ->where('isPrivate', 1);
        } elseif ($value == "private") {
            return $query->where('isPrivate', 1);
        }

        return $query;
    }

    public function getCounts(string $eventId): array
    {
        $airportIcao = EventAirport::where('eventId', $eventId)->pluck('icao')->toArray();

        $departureCount = Slot::where('eventId', $eventId)
            ->whereIn('origin', $airportIcao)
            ->whereNotIn('destination', $airportIcao)
            ->where('isPrivate', 0)
            ->count();

        $arrivalCount = Slot::where('eventId', $eventId)
            ->whereIn('destination', $airportIcao)
            ->whereNotIn('origin', $airportIcao)
            ->where('isPrivate', 0)
            ->count();

        $privateDepartureCount = Slot::where('eventId', $eventId)
            ->where('isFixedOrigin', 1)
            ->where('isFixedDestination', 0)
            ->where('isPrivate', 1)
            ->count();

        $privateArrivalCount = Slot::where('eventId', $eventId)
            ->where('isFixedOrigin', 0)
            ->where('isFixedDestination', 1)
            ->where('isPrivate', 1)
            ->count();

        return [
            'departure' => $departureCount,
            'landing' => $arrivalCount,
            'privateDeparture' => $privateDepartureCount,
            'privateLanding' => $privateArrivalCount,
        ];
    }
}
