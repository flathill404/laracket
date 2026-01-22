<?php

declare(strict_types=1);

namespace App\Queries;

use App\Models\Project;
use App\Values\TicketQuery;

class GetProjectTickets
{
    /**
     * @return \Illuminate\Contracts\Pagination\CursorPaginator<int, \App\Models\Ticket>
     */
    public function __invoke(Project $project, TicketQuery $ticketQuery): \Illuminate\Contracts\Pagination\CursorPaginator
    {
        $query = $project->tickets()
            ->with(['assignees', 'reviewers']);

        if (! empty($ticketQuery->statuses)) {
            $query->whereIn('status', $ticketQuery->statuses);
        }

        if ($ticketQuery->sort) {
            $query->orderBy($ticketQuery->sort, $ticketQuery->direction);
        }

        // Ensure deterministic order
        if ($ticketQuery->sort !== 'id') {
            $query->orderBy('id', 'desc');
        }

        return $query->cursorPaginate($ticketQuery->perPage);
    }
}
