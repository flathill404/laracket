<?php

namespace App\Actions\Project;

use App\Models\Project;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AddProjectMember
{
    /**
     * @throws ValidationException
     */
    public function __invoke(Project $project, User $user): void
    {
        $this->validateUserBelongsToOrganization($project, $user);
        $this->validateUserIsNotMember($project, $user);

        DB::transaction(function () use ($project, $user) {
            $project->assignedUsers()->attach($user);
        });
    }

    /**
     * Check if user belongs to the organization
     *
     * @throws ValidationException
     */
    protected function validateUserBelongsToOrganization(Project $project, User $user): void
    {
        if (! $project->organization->users()->where('user_id', $user->id)->exists()) {
            throw ValidationException::withMessages([
                'user' => ['This user does not belong to the project organization.'],
            ]);
        }
    }

    /**
     * Check if user is already a member of the project
     *
     * @throws ValidationException
     */
    protected function validateUserIsNotMember(Project $project, User $user): void
    {
        if ($project->assignedUsers()->where('user_id', $user->id)->exists()) {
            throw ValidationException::withMessages([
                'user' => ['This user is already a member of the project.'],
            ]);
        }
    }
}