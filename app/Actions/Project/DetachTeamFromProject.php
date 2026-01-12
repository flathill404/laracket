<?php

namespace App\Actions\Project;

use App\Models\Project;
use App\Models\Team;
use Illuminate\Support\Facades\DB;

class DetachTeamFromProject
{
    /**
     * @param  Project  $project
     * @param  Team  $team
     * @return void
     */
    public function __invoke(Project $project, Team $team): void
    {
        DB::transaction(function () use ($project, $team) {
            $project->assignedTeams()->detach($team);
        });
    }
}