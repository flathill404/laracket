<?php

namespace App\Actions\Organization;

use App\Models\Organization;

class UpdateOrganizationProfile
{
    public function __invoke(Organization $organization, array $data)
    {
        $organization->update($data);

        return $organization;
    }
}
