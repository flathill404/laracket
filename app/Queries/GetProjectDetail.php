<?php

namespace App\Queries;

use App\Models\Project;

class GetProjectDetail
{
    public function __invoke(Project $project): Project
    {
        return $project;
    }
}
