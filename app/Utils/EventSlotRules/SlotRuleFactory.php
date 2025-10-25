<?php

namespace App\Utils\EventSlotRules;

class SlotRuleFactory
{
    public static function make(string $eventType): SlotRuleInterface
    {
        switch (strtoupper($eventType)) {
            case 'RFO':
                return new RfoSlotRule();
            default:
                return new class implements SlotRuleInterface {
                    public function applyListTypeFilter($query, string $value): \Illuminate\Database\Eloquent\Builder
                    {
                        return $query;
                    }

                    public function getCounts(string $eventId): array
                    {
                        return [];
                    }
                };
        }
    }
}
