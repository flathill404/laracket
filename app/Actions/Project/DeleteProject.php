<?php

declare(strict_types=1);

namespace App\Actions\Project;

use App\Models\Project;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class DeleteProject
{
    public function __invoke(User $actor, Project $project): bool
    {
        $deleted = DB::transaction(function () use ($project) {
            return (bool) $project->delete();
        });

        return $deleted;
    }
}
