<?php

namespace App\Queries;

use App\Models\Organization;

class GetOrganizationDetail extends Query
{
    public function execute(Organization $organization)
    {
        return $organization;
    }
}
