<?php

namespace App\Actions\Team;

use App\Models\Organization;
use App\Models\Team;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class CreateTeam
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function create(User $creator, Organization $organization, array $data): Team
    {
        return DB::transaction(function () use ($organization, $data) {
            /** @var Team $team */
            $team = $organization->teams()->create([
                'name' => $data['name'],
                'display_name' => $data['display_name'],
            ]);

            if (isset($data['members'])) {
                $team->users()->attach($data['members'], ['role' => 'member']);
            }

            return $team;
        });
    }
}
