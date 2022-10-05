<?php

namespace App\Repository;

use App\Contracts\Data\EventRepositoryInterface;
use App\Models\Event;

class EventRepository implements EventRepositoryInterface
{
    /**
     * Gets an event by its id
     * @param int $id
     *
     * @return Event
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function getById(int $id): Event
    {
        return Event::query()->findOrFail($id);
    }
}

