<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\Response;

class UserPolicy
{
    public function list(User $user)
    {
        if (!$user->admin) {
            return Response::deny("admin.noAdmin");
        }

        return true;
    }

    public function update(User $user, String $userId)
    {
        if($user->id == $userId) {
            return Response::deny("admin.updateYourself");
        }

        if (!$user->admin) {
            return Response::deny("admin.noAdmin");
        }

        $targetUser = User::find($userId);

        if($targetUser->admin) {
            return Response::deny("admin.updateAdmin");
        }

        return true;
    }
}
