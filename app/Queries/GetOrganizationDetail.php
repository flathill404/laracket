<?php

namespace App\Queries;

use App\Models\Organization;

class GetOrganizationDetail
{
    public function execute(Organization $organization)
    {
        return $organization;
    }
}
