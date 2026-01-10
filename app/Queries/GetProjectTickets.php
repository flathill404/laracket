<?php

namespace App\Queries;

use App\Models\Project;

class GetProjectTickets
{
    public function __invoke(Project $project)
    {
        return $project->tickets;
    }
}
