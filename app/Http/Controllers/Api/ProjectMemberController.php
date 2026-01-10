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
    /**
     * @return \Illuminate\Database\Eloquent\Collection<int, \App\Models\User>
     */
    public function index(Project $project, GetProjectMembers $query): \Illuminate\Support\Collection
    {
        return $query($project);
    }

    public function store(Request $request, Project $project, AddProjectMember $action): \Illuminate\Http\Response
    {
        /** @var \App\Models\User $user */
        $user = User::findOrFail($request->input('user_id'));
        $action($project, $user);

        return response()->noContent();
    }

    public function destroy(Project $project, User $user, RemoveProjectMember $action): \Illuminate\Http\Response
    {
        $action($project, $user);

        return response()->noContent();
    }
}
