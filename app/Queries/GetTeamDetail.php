<?php

namespace App\Queries;

use App\Models\Team;

class GetTeamDetail
{
    public function __invoke(Team $team)
    {
        return $team;
    }
}
