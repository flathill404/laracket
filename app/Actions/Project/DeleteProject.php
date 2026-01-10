<?php

namespace App\Actions\Project;

use App\Models\Project;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class DeleteProject
{
    public function __invoke(User $actor, Project $project): bool
    {
        return DB::transaction(function () use ($project) {
            return (bool) $project->delete();
        });
    }
}
