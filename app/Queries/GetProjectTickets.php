<?php

namespace App\Queries;

use App\Models\Project;

class GetProjectTickets
{
    public function execute(Project $project)
    {
        return $project->tickets;
    }
}
