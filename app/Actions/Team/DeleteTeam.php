<?php

namespace App\Actions\Team;

use App\Models\Team;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class DeleteTeam
{
    public function __invoke(User $actor, Team $team): bool
    {
        return DB::transaction(function () use ($team) {
            return (bool) $team->delete();
        });
    }
}
