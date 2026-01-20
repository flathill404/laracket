<?php

namespace App\Queries;

use App\Models\Project;
use App\Values\TicketQuery;

class GetProjectTickets
{
    public function __invoke(Project $project, TicketQuery $ticketQuery): \Illuminate\Contracts\Pagination\CursorPaginator
    {
        $query = $project->tickets()
            ->with(['assignees', 'reviewers']);

        if (! empty($ticketQuery->statuses)) {
            $query->whereIn('status', $ticketQuery->statuses);
        }

        // Sorting
        $allowedSorts = ['id', 'created_at', 'updated_at', 'due_date'];

        if ($ticketQuery->sort && in_array($ticketQuery->sort, $allowedSorts)) {
            $query->orderBy($ticketQuery->sort, $ticketQuery->direction);
        }

        // Ensure deterministic order
        if ($ticketQuery->sort !== 'id') {
            $query->orderBy('id', 'asc');
        }

        return $query->cursorPaginate($ticketQuery->perPage);
    }
}
