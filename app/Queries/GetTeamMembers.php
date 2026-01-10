<?php

namespace App\Queries;

use App\Models\Team;

class GetTeamMembers extends Query
{
    public function execute(Team $team)
    {
        return $team->members;
    }
}
