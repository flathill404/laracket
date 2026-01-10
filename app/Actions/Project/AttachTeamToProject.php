<?php

namespace App\Actions\Project;

use App\Models\Project;
use App\Models\Team;
use Illuminate\Validation\ValidationException;

class AttachTeamToProject
{
    /**
     * @throws ValidationException
     */
    public function __invoke(Project $project, Team $team): void
    {
        // Check if team belongs to the organization
        if ($project->organization_id !== $team->organization_id) {
            throw ValidationException::withMessages([
                'team' => ['This team does not belong to the project organization.'],
            ]);
        }

        // Check if team is already assigned to the project
        if ($project->assignedTeams()->where('team_id', $team->id)->exists()) {
            throw ValidationException::withMessages([
                'team' => ['This team is already assigned to the project.'],
            ]);
        }

        $project->assignedTeams()->attach($team);
    }
}
