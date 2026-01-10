<?php

namespace App\Queries;

use App\Models\Project;

class GetProjectTickets extends Query
{
    public function execute(Project $project)
    {
        return $project->tickets;
    }
}
