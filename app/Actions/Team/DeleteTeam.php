<?php

declare(strict_types=1);

namespace App\Actions\Team;

use App\Models\Team;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class DeleteTeam
{
    public function __invoke(User $actor, Team $team): bool
    {
        $deleted = DB::transaction(function () use ($team) {
            return (bool) $team->delete();
        });

        return $deleted;
    }
}
