<?php

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

        if (Storage::disk('public')->exists($user->avatar_path)) {
            Storage::disk('public')->delete($user->avatar_path);
        }

        $user->forceFill([
            'avatar_path' => null,
        ])->save();
    }
}
