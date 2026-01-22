<?php

declare(strict_types=1);

namespace App\Queries;

use App\Models\Team;
use App\Models\Ticket;
use App\Values\TicketQuery;
use Illuminate\Contracts\Pagination\CursorPaginator;
use Illuminate\Database\Eloquent\Builder;

class GetTeamTickets
{
    /**
     * @return CursorPaginator<int, \App\Models\Ticket>
     */
    public function __invoke(Team $team, TicketQuery $ticketQuery): CursorPaginator
    {
        $query = Ticket::query()
            ->whereHas('project', function ($query) use ($team) {
                $query->whereHas('assignedTeams', function ($q) use ($team) {
                    $q->where('teams.id', $team->id);
                });
            })
            ->with(['project', 'assignees', 'reviewers'])
            ->when($ticketQuery->statuses, fn (Builder $query) => $query->whereIn('status', $ticketQuery->statuses));

        if ($ticketQuery->sort) {
            $query->orderBy($ticketQuery->sort, $ticketQuery->direction);
        }

        // Ensure deterministic order
        if ($ticketQuery->sort !== 'id') {
            $query->orderBy('id', 'asc');
        }

        return $query->cursorPaginate($ticketQuery->perPage);
    }
}
