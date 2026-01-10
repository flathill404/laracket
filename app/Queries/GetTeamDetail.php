<?php

namespace App\Queries;

use App\Models\Team;

class GetTeamDetail extends Query
{
    public function execute(Team $team)
    {
        return $team;
    }
}
