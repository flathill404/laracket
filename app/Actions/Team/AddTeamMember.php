<?php

namespace App\Actions\Team;

use App\Models\Team;
use App\Models\User;

class AddTeamMember
{
    public function __invoke(Team $team, User $user)
    {
        // Add member logic
    }
}
