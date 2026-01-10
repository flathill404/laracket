<?php

namespace App\Queries;

use App\Models\Team;

class GetTeamMembers
{
    public function execute(Team $team)
    {
        return $team->members;
    }
}
