<?php

namespace App\Actions\Project;

use App\Models\Project;
use App\Models\Team;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AttachTeamToProject
{
    /**
     * @throws ValidationException
     */
    public function __invoke(Project $project, Team $team): void
    {
        $this->validateTeamBelongsToOrganization($project, $team);
        $this->validateTeamIsNotAssigned($project, $team);

        DB::transaction(function () use ($project, $team) {
            $project->assignedTeams()->attach($team);
        });
    }

    /**
     * Check if team belongs to the organization
     *
     * @throws ValidationException
     */
    protected function validateTeamBelongsToOrganization(Project $project, Team $team): void
    {
        if ($project->organization_id !== $team->organization_id) {
            throw ValidationException::withMessages([
                'team' => ['This team does not belong to the project organization.'],
            ]);
        }
    }

    /**
     * Check if team is already assigned to the project
     *
     * @throws ValidationException
     */
    protected function validateTeamIsNotAssigned(Project $project, Team $team): void
    {
        if ($project->assignedTeams()->where('team_id', $team->id)->exists()) {
            throw ValidationException::withMessages([
                'team' => ['This team is already assigned to the project.'],
            ]);
        }
    }
}