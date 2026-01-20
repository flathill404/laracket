<?php

namespace App\Queries;

use App\Models\Team;
use App\Models\Ticket;
use Illuminate\Database\Eloquent\Builder;

class GetTeamTickets
{
    /**
     * @param  array<\App\Enums\TicketStatus>  $statuses
     */
    public function __invoke(Team $team, array $statuses = [], ?string $sort = 'id', int $perPage = 25): \Illuminate\Contracts\Pagination\CursorPaginator
    {
        $query = Ticket::query()
            ->whereHas('project', function ($query) use ($team) {
                $query->whereHas('assignedTeams', function ($q) use ($team) {
                    $q->where('teams.id', $team->id);
                });
            })
            ->with(['project', 'assignees', 'reviewers'])
            ->when($statuses, fn (Builder $query) => $query->whereIn('status', $statuses));

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
