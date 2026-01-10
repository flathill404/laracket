<?php

namespace App\Queries;

use App\Models\Team;

class GetTeamDetail
{
    public function execute(Team $team)
    {
        return $team;
    }
}
