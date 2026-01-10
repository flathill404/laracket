<?php

namespace App\Queries;

use App\Models\Project;
use Illuminate\Database\Eloquent\Collection;

class GetProjectMembers
{
    /**
     * @return Collection<int, \App\Models\User>
     */
    public function __invoke(Project $project): Collection
    {
        return $project->members;
    }
}
