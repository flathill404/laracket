<?php

namespace App\Queries;

use App\Models\Organization;

class GetOrganizationTeams
{
    public function __invoke(Organization $organization)
    {
        return $organization->teams;
    }
}
