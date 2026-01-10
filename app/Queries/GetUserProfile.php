<?php

namespace App\Queries;

use App\Models\User;

class GetUserProfile extends Query
{
    public function execute(User $user)
    {
        return $user;
    }
}
