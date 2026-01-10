<?php

namespace App\Queries;

use App\Models\Organization;

class GetOrganizationTeams
{
    public function execute(Organization $organization)
    {
        return $organization->teams;
    }
}
