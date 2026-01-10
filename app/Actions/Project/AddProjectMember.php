<?php

namespace App\Actions\Project;

use App\Models\Project;
use App\Models\User;
use Illuminate\Validation\ValidationException;

class AddProjectMember
{
    /**
     * @throws ValidationException
     */
    public function __invoke(Project $project, User $user): void
    {
        // Check if user belongs to the organization
        if (! $project->organization->users()->where('user_id', $user->id)->exists()) {
            throw ValidationException::withMessages([
                'user' => ['This user does not belong to the project organization.'],
            ]);
        }

        // Check if user is already a member of the project
        if ($project->assignedUsers()->where('user_id', $user->id)->exists()) {
            throw ValidationException::withMessages([
                'user' => ['This user is already a member of the project.'],
            ]);
        }

        $project->assignedUsers()->attach($user);
    }
}
