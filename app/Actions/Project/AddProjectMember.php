<?php

namespace App\Actions\Project;

use App\Models\Project;
use App\Models\User;

class AddProjectMember
{
    public function __invoke(Project $project, User $user)
    {
        // Add member logic
    }
}
