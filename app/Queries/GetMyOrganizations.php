<?php

namespace App\Queries;

use App\Models\User;

class GetMyOrganizations
{
    public function execute(User $user)
    {
        // Assuming relationship exists
        return $user->organizations;
    }
}
