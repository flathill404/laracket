<?php

namespace App\Queries;

use App\Models\Organization;

class GetOrganizationTeams extends Query
{
    public function execute(Organization $organization)
    {
        return $organization->teams;
    }
}
