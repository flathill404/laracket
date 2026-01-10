<?php

namespace App\Http\Controllers\Api;

use App\Actions\Project\AddProjectMember;
use App\Actions\Project\RemoveProjectMember;
use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\User;
use App\Queries\GetProjectMembers;
use Illuminate\Http\Request;

class ProjectMemberController extends Controller
{
    public function index(Project $project, GetProjectMembers $query)
    {
        return $query->execute($project);
    }

    public function store(Request $request, Project $project, AddProjectMember $action)
    {
        $action($project, User::find($request->input('user_id')));

        return response()->noContent();
    }

    public function destroy(Project $project, User $user, RemoveProjectMember $action)
    {
        $action($project, $user);

        return response()->noContent();
    }
}
