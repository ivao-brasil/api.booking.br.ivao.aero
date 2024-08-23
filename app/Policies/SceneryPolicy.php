<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\Response;
use Illuminate\Support\Facades\Log;

class SceneryPolicy
{
    public function create(User $user) {
        if(!$user->admin) {
            Log::info(SceneryPolicy::class . ' [CREATE] User is not admin');
            return Response::deny("admin.noAdmin");
        }

        return true;
    }

    public function update(User $user) {
        if(!$user->admin) {
            Log::info(SceneryPolicy::class . ' [UPDATE] User is not admin');
            return Response::deny("admin.noAdmin");
        }

        return true;
    }

    public function delete(User $user) {
        if(!$user->admin) {
            Log::info(SceneryPolicy::class . ' [DELETE] User is not admin');
            return Response::deny("admin.noAdmin");
        }

        return true;
    }

    public function getAll(User $user) {
        if(!$user->admin) {
            Log::info(SceneryPolicy::class . ' [GETALL] User is not admin');
            return Response::deny("admin.noAdmin");
        }

        return true;
    }
}
