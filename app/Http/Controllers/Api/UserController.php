<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Resources\UserResource;
use App\Queries\GetUserProfile;
use Illuminate\Http\Request;

class UserController
{
    public function show(Request $request, GetUserProfile $query): UserResource
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

        $user = $query($user);

        return new UserResource($user);
    }
}
