<?php

namespace App\Queries;

use App\Models\Project;

class GetProjectDetail extends Query
{
    public function execute(Project $project)
    {
        return $project;
    }
}
