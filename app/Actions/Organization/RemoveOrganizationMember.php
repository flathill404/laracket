<?php

namespace App\Actions\Organization;

use App\Models\Organization;
use App\Models\User;

class RemoveOrganizationMember
{
    public function __invoke(Organization $organization, User $user)
    {
        // Remove logic
    }
}
