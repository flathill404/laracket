<?php

namespace App\Queries;

use App\Models\Team;
use App\Models\Ticket;
use App\Values\TicketQuery;
use Illuminate\Database\Eloquent\Builder;

class GetTeamTickets
{
    public function __invoke(Team $team, TicketQuery $ticketQuery): \Illuminate\Contracts\Pagination\CursorPaginator
    {
        $query = Ticket::query()
            ->whereHas('project', function ($query) use ($team) {
                $query->whereHas('assignedTeams', function ($q) use ($team) {
                    $q->where('teams.id', $team->id);
                });
            })
            ->with(['project', 'assignees', 'reviewers'])
            ->when($ticketQuery->statuses, fn (Builder $query) => $query->whereIn('status', $ticketQuery->statuses));

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
