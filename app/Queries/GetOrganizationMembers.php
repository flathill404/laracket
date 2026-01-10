<?php

namespace App\Queries;

use App\Models\Organization;

class GetOrganizationMembers extends Query
{
    public function execute(Organization $organization)
    {
        return $organization->users;
    }
}
