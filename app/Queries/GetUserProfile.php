<?php

namespace App\Queries;

use App\Models\User;

class GetUserProfile
{
    public function execute(User $user)
    {
        return $user;
    }
}
