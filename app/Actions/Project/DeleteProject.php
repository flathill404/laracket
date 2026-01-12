<?php

namespace App\Actions\Project;

use App\Models\Project;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class DeleteProject
{
    /**
     * @param  User  $actor
     * @param  Project  $project
     * @return bool
     */
    public function __invoke(User $actor, Project $project): bool
    {
        $deleted = DB::transaction(function () use ($project) {
            return (bool) $project->delete();
        });

        return $deleted;
    }
}