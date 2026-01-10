<?php

namespace App\Queries;

use App\Models\Project;

class GetProjectMembers extends Query
{
    public function execute(Project $project)
    {
        return $project->members;
    }
}
