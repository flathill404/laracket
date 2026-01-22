<?php

declare(strict_types=1);

namespace App\Queries;

use App\Models\Organization;
use Illuminate\Database\Eloquent\Collection;

class GetOrganizationMembers
{
    /**
     * @return Collection<int, \App\Models\User>
     */
    public function __invoke(Organization $organization): Collection
    {
        return $organization->users;
    }
}
