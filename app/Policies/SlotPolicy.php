<?php

namespace App\Policies;

use App\Http\Controllers\SlotController;
use App\Models\Slot;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Auth\Access\Response;
use Illuminate\Support\Facades\Log;

class SlotPolicy
{
    public function create(User $user)
    {
        if (!$user->admin) {
            Log::info(SlotPolicy::class . ' [CREATE] User is not admin');
            return Response::deny("admin.noAdmin");
        }

        return true;
    }

    public function delete(User $user)
    {
        if (!$user->admin) {
            Log::info(SlotPolicy::class . ' [DELETE] User is not admin');
            return Response::deny("admin.noAdmin");
        }

        return true;
    }

    public function bookUpdate(User $user, Slot $slot, string $action)
    {

        if ($user->suspended) {
            Log::info(SlotPolicy::class . " [BOOK UPDATE] User is suspended", $user->toArray());
            return Response::deny('book.suspended');
        }

        if (!is_null($slot->pilotId) && $slot->pilotId !== $user->id) {
            Log::info(SlotPolicy::class . " [BOOK UPDATE] User is not the owner of the slot", $user->toArray());
            return Response::deny("book.notOwner");
        }

        /** @var \App\Models\Event */
        $slotEvent = $slot->event;

        if($slotEvent->status !== 'scheduled') {
            Log::info(SlotPolicy::class . " [BOOK UPDATE] Event is not scheduled", $slotEvent->toArray());
            return Response::deny("book.notActive");
        }

        if ($slotEvent->has_ended) {
            Log::info(SlotPolicy::class . " [BOOK UPDATE] Event has ended", $slotEvent->toArray());
            return Response::deny("book.hasEnded");
        }

        if ($action === "confirm") {
            if ($slot->bookingStatus !== "prebooked") {
                Log::info(SlotPolicy::class . " [BOOK UPDATE] The slot is not prebooked", $slot->toArray());
                return Response::deny("The slot is not prebooked", 400);
            }

            if (!$slotEvent->can_confirm_slots) {
                return Response::deny("book.tooEarly");
            }
        }

        if ($action === "book") {
            if ($slotEvent->has_started && !$slotEvent->allowBookingAfterStart) {
                Log::info(SlotPolicy::class . " [BOOK UPDATE] Event has started", $slotEvent->toArray());
                return Response::deny("book.hasStarted");
            }
        }

        return true;
    }

    public function update(User $user)
    {
        if (!$user->admin) {
            Log::info(SlotPolicy::class . ' [UPDATE] User is not admin');
            return Response::deny("admin.noAdmin");
        }

        return true;
    }

    public function listOverlapping(User $user)
    {
        if (!$user->admin) {
            Log::info(SlotPolicy::class . ' [LIST OVERLAPPING] User is not admin');
            return Response::deny("admin.noAdmin");
        }

        return true;
    }
}
