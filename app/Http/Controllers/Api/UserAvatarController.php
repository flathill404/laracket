<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Actions\User\DeleteUserAvatar;
use App\Actions\User\UpdateUserAvatar;
use App\Http\Resources\UserResource;
use Illuminate\Http\Request;

class UserAvatarController
{
    public function update(Request $request, UpdateUserAvatar $action): UserResource
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

        /** @var array<string, mixed> $input */
        $input = $request->all();

        $action($user, $input);

        return new UserResource($user);
    }

    public function destroy(Request $request, DeleteUserAvatar $action): \Illuminate\Http\Response
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

        $action($user);

        return response()->noContent();
    }
}
