<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Actions\User\DeleteUserAvatar;
use App\Actions\User\UpdateUserAvatar;
use App\Http\Resources\UserResource;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class UserAvatarController
{
    public function update(Request $request, UpdateUserAvatar $action): UserResource
    {
        $user = $request->user();
        assert($user instanceof \App\Models\User);

        /** @var array<string, mixed> $input */
        $input = $request->all();

        $action($user, $input);

        return new UserResource($user);
    }

    public function destroy(Request $request, DeleteUserAvatar $action): Response
    {
        $user = $request->user();
        assert($user instanceof \App\Models\User);

        $action($user);

        return response()->noContent();
    }
}
