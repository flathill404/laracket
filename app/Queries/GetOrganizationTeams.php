<?php

declare(strict_types=1);

namespace App\Queries;

use App\Models\Organization;
use Illuminate\Database\Eloquent\Collection;

class GetOrganizationTeams
{
    /**
     * @return Collection<int, \App\Models\Team>
     */
    public function __invoke(Organization $organization): Collection
    {
        return $organization->teams;
    }
}
