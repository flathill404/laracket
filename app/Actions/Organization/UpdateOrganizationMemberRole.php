<?php

namespace App\Actions\Organization;

use App\Models\Organization;
use App\Models\User;

class UpdateOrganizationMemberRole
{
    public function __invoke(Organization $organization, User $user, string $role)
    {
        // Update logic
    }
}
