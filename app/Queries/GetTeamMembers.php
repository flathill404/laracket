<?php

namespace App\Queries;

use App\Models\Team;

class GetTeamMembers
{
    public function __invoke(Team $team)
    {
        return $team->members;
    }
}
