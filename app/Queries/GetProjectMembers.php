<?php

namespace App\Queries;

use App\Models\Project;

class GetProjectMembers
{
    public function __invoke(Project $project)
    {
        return $project->members;
    }
}
