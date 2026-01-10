<?php

namespace App\Actions\Team;

use App\Models\Team;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class UpdateTeam
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function update(User $actor, Team $team, array $data): Team
    {
        return DB::transaction(function () use ($team, $data) {
            $team->update([
                'name' => $data['name'] ?? $team->name,
                'display_name' => $data['display_name'] ?? $team->display_name,
            ]);

            if (isset($data['members'])) {
                $team->users()->syncWithPivotValues($data['members'], ['role' => 'member']);
            }

            return $team;
        });
    }
}
