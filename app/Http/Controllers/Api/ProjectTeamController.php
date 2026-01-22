<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Actions\Project\AttachTeamToProject;
use App\Actions\Project\DetachTeamFromProject;
use App\Models\Project;
use App\Models\Team;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class ProjectTeamController
{
    public function store(Request $request, Project $project, AttachTeamToProject $action): \Illuminate\Http\Response
    {
        Gate::authorize('attach_team', $project);

        /** @var \App\Models\Team $team */
        $team = Team::findOrFail($request->input('team_id'));
        $action($project, $team);

        return response()->noContent();
    }

    public function destroy(Project $project, Team $team, DetachTeamFromProject $action): \Illuminate\Http\Response
    {
        Gate::authorize('detach_team', $project);

        $action($project, $team);

        return response()->noContent();
    }
}
