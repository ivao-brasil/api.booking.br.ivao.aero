<?php

namespace App\Policies;

use App\Models\Event;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;
use Illuminate\Support\Facades\Log;

class EventDataExportPolicy
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
            Log::info(EventDataExportPolicy::class . ' [EXPORT DATA] User is not admin');
            return $this->deny();
        }

        if (!$event->has_ended) {
            Log::info(EventDataExportPolicy::class . ' [EXPORT DATA] Event has not ended');
            return $this->deny();
        }

        return $this->allow();
    }
}
