<?php

namespace App\Actions\Project;

use App\Models\Project;
use App\Models\Team;

class AttachTeamToProject
{
    public function __invoke(Project $project, Team $team)
    {
        // Attach logic
    }
}
