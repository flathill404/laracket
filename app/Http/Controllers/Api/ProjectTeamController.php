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
    public function store(Request $request, Project $project, AttachTeamToProject $action)
    {
        $action($project, Team::find($request->input('team_id')));

        return response()->noContent();
    }

    public function destroy(Project $project, Team $team, DetachTeamFromProject $action)
    {
        $action($project, $team);

        return response()->noContent();
    }
}
