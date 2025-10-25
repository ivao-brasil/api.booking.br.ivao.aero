<?php

namespace App\Utils\EventSlotRules;

use Illuminate\Database\Eloquent\Builder;

interface SlotRuleInterface
{
    public function applyListTypeFilter(Builder $query, string $value): Builder;

    public function getCounts(string $eventId): array;
}
