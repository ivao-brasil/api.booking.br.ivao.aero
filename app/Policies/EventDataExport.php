<?php

namespace App\Policies;

use App\Models\Event;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;

class EventDataExport
{
    use HandlesAuthorization;

    /**
     * Verifies if the event data can be exported
     *
     * @param User $user
     * @param Event $event
     * @return Response
     */
    public function export(User $user, Event $event): Response
    {
        if (!$user->admin) {
            return $this->deny();
        }

        if (!$event->has_ended) {
            return $this->deny();
        }

        return $this->allow();
    }
}
