<?php

namespace App\Actions\Project;

use App\Models\Project;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class RemoveProjectMember
{
    /**
     * @param  Project  $project
     * @param  User  $user
     * @return void
     */
    public function __invoke(Project $project, User $user): void
    {
        DB::transaction(function () use ($project, $user) {
            $project->assignedUsers()->detach($user);
        });
    }
}