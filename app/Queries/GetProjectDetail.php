<?php

namespace App\Queries;

use App\Models\Project;

class GetProjectDetail
{
    public function execute(Project $project)
    {
        return $project;
    }
}
