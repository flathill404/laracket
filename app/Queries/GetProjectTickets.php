<?php

namespace App\Queries;

use App\Models\Project;

class GetProjectTickets
{
    /**
     * @param  array<\App\Enums\TicketStatus>  $statuses
     */
    public function __invoke(Project $project, array $statuses = [], ?string $sort = 'id', int $perPage = 25): \Illuminate\Contracts\Pagination\CursorPaginator
    {
        $query = $project->tickets()
            ->with(['assignees', 'reviewers']);

        if (! empty($statuses)) {
            $query->whereIn('status', $statuses);
        }

        // Sorting
        $allowedSorts = ['id', 'created_at', 'updated_at', 'due_date'];
        $direction = 'asc';

        if ($sort) {
            if (str_starts_with($sort, '-')) {
                $direction = 'desc';
                $sort = substr($sort, 1);
            }

            if (in_array($sort, $allowedSorts)) {
                $query->orderBy($sort, $direction);
            }
        }

        // Ensure deterministic order
        if ($sort !== 'id') {
            $query->orderBy('id', 'asc');
        }

        return $query->cursorPaginate($perPage);
    }
}
