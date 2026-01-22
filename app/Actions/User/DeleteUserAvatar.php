<?php

declare(strict_types=1);

namespace App\Actions\User;

use App\Models\User;
use Illuminate\Support\Facades\Storage;

class DeleteUserAvatar
{
    public function __invoke(User $user): void
    {
        if (! $user->avatar_path) {
            return;
        }

        if (Storage::exists($user->avatar_path)) {
            Storage::delete($user->avatar_path);
        }

        $user->forceFill([
            'avatar_path' => null,
        ])->save();
    }
}
