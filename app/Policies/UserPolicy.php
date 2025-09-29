<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\Response;
use Illuminate\Support\Facades\Log;

class UserPolicy
{
    public function list(User $user)
    {
        if (!$user->admin) {
            Log::info(UserPolicy::class . ' [LIST] User is not admin');
            return Response::deny("admin.noAdmin");
        }

        return true;
    }

    public function update(User $user, String $userId)
    {
        if($user->id == $userId) {
            Log::info(UserPolicy::class . ' [UPDATE] User is updating itself', $user->toArray());
            return Response::deny("admin.updateYourself");
        }

        if (!$user->admin) {
            Log::info(UserPolicy::class . ' [UPDATE] User is not admin');
            return Response::deny("admin.noAdmin");
        }

        $targetUser = User::find($userId);

        if($targetUser->admin) {
            Log::info(UserPolicy::class . ' [UPDATE] Target user is admin', ['targetUser' => $targetUser->toArray(), 'userActed' => $user->toArray()]);
            return Response::deny("admin.updateAdmin");
        }

        return true;
    }
}
