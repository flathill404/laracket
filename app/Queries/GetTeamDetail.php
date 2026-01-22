<?php

declare(strict_types=1);

namespace App\Queries;

use App\Models\Team;

class GetTeamDetail
{
    public function __invoke(Team $team): Team
    {
        return $team;
    }
}
