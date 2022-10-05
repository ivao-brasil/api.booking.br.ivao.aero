<?php

namespace App\Contracts\Data;

use App\Models\Event;

interface EventRepositoryInterface
{
    /**
     * Gets an event by its id
     * @param int $id
     *
     * @return Event
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function getById(int $id): Event;
}
