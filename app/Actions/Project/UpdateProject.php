<?php

namespace App\Actions\Project;

use App\Models\Project;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class UpdateProject
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function update(User $actor, Project $project, array $data): Project
    {
        return DB::transaction(function () use ($project, $data) {
            $project->update([
                'name' => $data['name'] ?? $project->name,
                'display_name' => $data['display_name'] ?? $project->display_name,
                'description' => $data['description'] ?? $project->description,
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
