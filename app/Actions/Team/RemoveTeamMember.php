<?php

namespace App\Actions\Team;

use App\Models\Team;
use App\Models\User;

class RemoveTeamMember
{
    public function __invoke(Team $team, User $user)
    {
        // Remove logic
    }
}
