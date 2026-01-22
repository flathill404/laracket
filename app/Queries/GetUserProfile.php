<?php

declare(strict_types=1);

namespace App\Queries;

use App\Models\User;

class GetUserProfile
{
    public function __invoke(User $user): User
    {
        return User::findOrFail($user->id);
    }
}
