<?php

namespace App\Actions\Project;

use App\Models\Organization;
use App\Models\Project;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class CreateProject
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function create(User $creator, Organization $organization, array $data): Project
    {
        return DB::transaction(function () use ($organization, $data) {
            /** @var Project $project */
            $project = $organization->projects()->create([
                'name' => $data['name'],
                'display_name' => $data['display_name'],
                'description' => $data['description'] ?? null,
            ]);

            if (isset($data['assigned_users'])) {
                $project->assignedUsers()->sync($data['assigned_users']);
            }

            if (isset($data['assigned_teams'])) {
                $project->assignedTeams()->sync($data['assigned_teams']);
            }

            return $project;
        });
    }
}
