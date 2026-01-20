<?php

namespace App\Queries;

use App\Models\Project;
use Illuminate\Database\Eloquent\Collection;

class GetProjectTickets
{
    /**
     * @param  array<int, \App\Enums\TicketStatus>  $statuses
     * @return Collection<int, \App\Models\Ticket>
     */
    public function __invoke(Project $project, array $statuses = []): Collection
    {
        $query = $project->tickets()
            ->with(['assignees', 'reviewers']);

        if (! empty($statuses)) {
            $query->whereIn('status', $statuses);
        }

        return $query->get();
    }
}
