<?php

namespace App\Queries;

use App\Models\Organization;

class GetOrganizationProjects
{
    public function __invoke(Organization $organization)
    {
        return $organization->projects;
    }
}
