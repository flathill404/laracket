<?php

namespace App\Queries;

use App\Models\Team;
use App\Models\Ticket;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class GetTeamTickets
{
    /**
     * @param  array<\App\Enums\TicketStatus>  $statuses
     * @return Collection<int, Ticket>
     */
    public function __invoke(Team $team, array $statuses = []): Collection
    {
        $query = Ticket::query()
            ->whereHas('project', function ($query) use ($team) {
                $query->whereHas('assignedTeams', function ($q) use ($team) {
                    $q->where('teams.id', $team->id);
                });
            })
            ->with(['project', 'assignees', 'reviewers'])
            ->when($statuses, fn (Builder $query) => $query->whereIn('status', $statuses));

        return $query->get();
    }
}
