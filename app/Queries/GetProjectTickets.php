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
        return $project->tickets;
    }
}
