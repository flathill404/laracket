<?php

namespace App\Queries;

use App\Models\Project;
use Illuminate\Database\Eloquent\Collection;

class GetProjectTickets
{
    /**
     * @return Collection<int, \App\Models\Ticket>
     */
    public function __invoke(Project $project): Collection
    {
        $project->load('tickets');
        $project->tickets->load('assignees');
        $project->tickets->load('reviewers');
        return $project->tickets;
    }
}
