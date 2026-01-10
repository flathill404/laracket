<?php

namespace App\Queries;

use App\Models\Team;
use Illuminate\Database\Eloquent\Collection;

class GetTeamMembers
{
    /**
     * @return Collection<int, \App\Models\User>
     */
    public function __invoke(Team $team): Collection
    {
        return $team->members;
    }
}
