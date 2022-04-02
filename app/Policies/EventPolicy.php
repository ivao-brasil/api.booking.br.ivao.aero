<?php

namespace App\Policies;

use App\Models\Event;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Auth\Access\Response;

class EventPolicy
{
    public function create(User $user, Carbon $dateStart, Carbon $dateEnd)
    {
        if($dateStart->diffInHours($dateEnd > 10))
            return Response::deny('event.tooLong');

        return $user->admin
            ? Response::allow()
            : Response::deny("admin.noAdmin");
    }

    public function update(User $user, Event $event)
    {
        if (!$user->admin) {
            return Response::deny("admin.noAdmin");
        }

        if ($event->status === 'finished') {
            return Response::deny('admin.eventFinisheed');
        }

        if ($user->division !== $event->division) {
            return Response::deny('admin.wrongDivision');
        }

        return true;
    }

    public function delete(User $user, Event $event)
    {
        if (!$user->admin) {
            return Response::deny('admin.noAdmin');
        }

        if ($event->status === 'scheduled') {
            return Response::deny('admin.isActive');
        }

        if ($user->division !== $event->division) {
            return Response::deny('admin.wrongDivision');
        }

        return true;
    }
}
