<?php

namespace App\Http\Controllers\Api;

use App\Actions\Project\AttachTeamToProject;
use App\Actions\Project\DetachTeamFromProject;
use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\Team;
use Illuminate\Http\Request;

class ProjectTeamController extends Controller
{
    public function store(Request $request, Project $project, AttachTeamToProject $action): \Illuminate\Http\Response
    {
        /** @var \App\Models\Team $team */
        $team = Team::findOrFail($request->input('team_id'));
        $action($project, $team);

        return response()->noContent();
    }

    public function destroy(Project $project, Team $team, DetachTeamFromProject $action): \Illuminate\Http\Response
    {
        $action($project, $team);

        return response()->noContent();
    }
}
