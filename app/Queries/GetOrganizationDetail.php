<?php

namespace App\Queries;

use App\Models\Organization;

class GetOrganizationDetail
{
    public function __invoke(Organization $organization): Organization
    {
        return $organization;
    }
}
