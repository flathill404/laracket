<?php

namespace App\Actions\Project;

use App\Models\Project;
use App\Models\Team;

class DetachTeamFromProject
{
    public function __invoke(Project $project, Team $team)
    {
        // Detach logic
    }
}
