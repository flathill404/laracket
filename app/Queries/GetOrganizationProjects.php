<?php

namespace App\Queries;

use App\Models\Organization;

class GetOrganizationProjects extends Query
{
    public function execute(Organization $organization)
    {
        return $organization->projects;
    }
}
