<?php

namespace App\Actions\User;

use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UpdateUserPassword
{
    public function __invoke(User $user, string $newPassword)
    {
        $user->update([
            'password' => Hash::make($newPassword),
        ]);
    }
}
