<?php

declare(strict_types=1);

namespace App\Queries;

use App\Models\Organization;
use Illuminate\Database\Eloquent\Collection;

class GetOrganizationProjects
{
    /**
     * @return Collection<int, \App\Models\Project>
     */
    public function __invoke(Organization $organization): Collection
    {
        return $organization->projects;
    }
}
