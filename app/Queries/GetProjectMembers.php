<?php

namespace App\Queries;

use App\Models\Project;

class GetProjectMembers
{
    public function execute(Project $project)
    {
        return $project->members;
    }
}
