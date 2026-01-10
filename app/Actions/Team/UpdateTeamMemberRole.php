<?php

namespace App\Actions\Team;

use App\Models\Team;
use App\Models\User;

class UpdateTeamMemberRole
{
    public function __invoke(Team $team, User $user, string $role)
    {
        // Update logic
    }
}
