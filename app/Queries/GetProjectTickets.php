<?php

namespace App\Queries;

use App\Enums\TicketStatus;
use App\Models\Project;
use Illuminate\Database\Eloquent\Collection;

class GetProjectTickets
{
    /**
     * @param  array<int, \App\Enums\TicketStatus>|\App\Enums\TicketStatus|null  $statuses
     * @return Collection<int, \App\Models\Ticket>
     */
    public function __invoke(Project $project, array|TicketStatus|null $statuses = null): Collection
    {
        $query = $project->tickets()
            ->with(['assignees', 'reviewers']);

        if ($statuses) {
            $statuses = is_array($statuses) ? $statuses : [$statuses];
            $query->whereIn('status', $statuses);
        }

        return $query->get();
    }
}
