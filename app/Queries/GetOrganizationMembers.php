<?php

namespace App\Queries;

use App\Models\Organization;

class GetOrganizationMembers
{
    public function __invoke(Organization $organization)
    {
        return $organization->users;
    }
}
