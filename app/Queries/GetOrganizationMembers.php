<?php

namespace App\Queries;

use App\Models\Organization;

class GetOrganizationMembers
{
    public function execute(Organization $organization)
    {
        return $organization->users;
    }
}
