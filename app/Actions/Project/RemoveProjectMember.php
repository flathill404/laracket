<?php

namespace App\Actions\Project;

use App\Models\Project;
use App\Models\User;

class RemoveProjectMember
{
    public function __invoke(Project $project, User $user): void
    {
        $project->assignedUsers()->detach($user);
    }
}
