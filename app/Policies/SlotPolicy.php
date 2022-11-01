<?php

namespace App\Policies;

use App\Http\Controllers\SlotController;
use App\Models\Slot;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Auth\Access\Response;

class SlotPolicy
{
    public function create(User $user)
    {
        if (!$user->admin) {
            return Response::deny("admin.noAdmin");
        }

        return true;
    }

    public function delete(User $user)
    {
        if (!$user->admin) {
            return Response::deny("admin.noAdmin");
        }

        return true;
    }

    public function bookUpdate(User $user, Slot $slot, string $action)
    {

        if ($user->suspended) {
            return Response::deny('book.suspended');
        }

        if (!is_null($slot->pilotId) && $slot->pilotId !== $user->id) {
            return Response::deny("book.notOwner");
        }

        /** @var \App\Models\Event */
        $slotEvent = $slot->event;

        if($slotEvent->status !== 'scheduled') {
            return Response::deny("book.notActive");
        }

        if ($slotEvent->has_ended) {
            return Response::deny("book.hasEnded");
        }

        if ($action === "confirm") {
            if ($slot->bookingStatus !== "prebooked") {
                return Response::deny("The slot is not prebooked", 400);
            }

            if (!$slotEvent->can_confirm_slots) {
                return Response::deny("book.tooEarly");
            }
        }

        if ($action === "book") {
            if ($slotEvent->has_started && !$slotEvent->allowBookingAfterStart) {
                return Response::deny("book.hasStarted");
            }
        }

        return true;
    }

    public function update(User $user)
    {
        if (!$user->admin) {
            return Response::deny("admin.noAdmin");
        }

        return true;
    }

    public function listOverlapping(User $user)
    {
        if (!$user->admin) {
            return Response::deny("admin.noAdmin");
        }

        return true;
    }
}
