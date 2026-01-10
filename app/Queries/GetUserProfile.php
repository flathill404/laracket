<?php

namespace App\Queries;

use App\Models\User;

class GetUserProfile
{
    public function __invoke(User $user): User
    {
        return $user;
    }
}
