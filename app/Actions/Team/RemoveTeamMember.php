<?php

declare(strict_types=1);

namespace App\Actions\Team;

use App\Models\Team;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class RemoveTeamMember
{
    public function __invoke(Team $team, User $user): void
    {
        DB::transaction(function () use ($team, $user) {
            $team->users()->detach($user);
        });
    }
}
