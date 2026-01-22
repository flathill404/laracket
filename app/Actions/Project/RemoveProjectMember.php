<?php

declare(strict_types=1);

namespace App\Actions\Project;

use App\Models\Project;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class RemoveProjectMember
{
    public function __invoke(Project $project, User $user): void
    {
        DB::transaction(function () use ($project, $user) {
            $project->assignedUsers()->detach($user);
        });
    }
}
