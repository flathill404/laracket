<?php

namespace App\Queries;

use App\Models\Organization;

class GetOrganizationProjects
{
    public function execute(Organization $organization)
    {
        return $organization->projects;
    }
}
