<?php

namespace App\Policies;

use App\Models\Event;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Auth\Access\Response;
use Illuminate\Support\Facades\Log;

class EventPolicy
{
    public function create(User $user)
    {
        if (!$user->admin) {
            Log::info(EventPolicy::class . ' [CREATE] User is not admin');
            return Response::deny('admin.noAdmin');
        }
        return true;
    }

    public function update(User $user, Event $event)
    {
        if (!$user->admin) {
            Log::info(EventPolicy::class . ' [UPDATE] User is not admin');
            return Response::deny("admin.noAdmin");
        }

        if ($event->status === 'finished') {
            Log::info(EventPolicy::class . ' [UPDATE] Event is finished');
            return Response::deny('admin.eventFinisheed');
        }

        if ($user->division !== $event->division) {
            Log::info(EventPolicy::class . ' [UPDATE] User is in wrong division');
            return Response::deny('admin.wrongDivision');
        }

        return true;
    }

    public function delete(User $user, Event $event)
    {
        if (!$user->admin) {
            Log::info(EventPolicy::class . ' [DELETE] User is not admin');
            return Response::deny('admin.noAdmin');
        }

        if ($event->status === 'scheduled') {
            Log::info(EventPolicy::class . ' [DELETE] Event is scheduled');
            return Response::deny('admin.isActive');
        }

        if ($user->division !== $event->division) {
            Log::info(EventPolicy::class . ' [DELETE] User is in wrong division');
            return Response::deny('admin.wrongDivision');
        }

        return true;
    }
}
