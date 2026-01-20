<?php

namespace App\Queries;

use App\Models\Team;
use App\Models\Ticket;
use Illuminate\Database\Eloquent\Collection;

class GetTeamTickets
{
    /**
     * @return Collection<int, Ticket>
     */
    public function __invoke(Team $team): Collection
    {
        $query = Ticket::query()
            ->whereHas('project', function ($query) use ($team) {
                $query->whereHas('assignedTeams', function ($q) use ($team) {
                    $q->where('teams.id', $team->id);
                });
            })
            ->with(['project', 'assignees', 'reviewers']);

        return $query->get();
    }
}
