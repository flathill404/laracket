<?php

namespace App\Http\Controllers\Api;

use App\Actions\Project\AddProjectMember;
use App\Actions\Project\RemoveProjectMember;
use App\Models\Project;
use App\Models\User;
use App\Queries\GetProjectMembers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class ProjectMemberController
{
    /**
     * @return \Illuminate\Database\Eloquent\Collection<int, \App\Models\User>
     */
    public function index(Project $project, GetProjectMembers $query): \Illuminate\Support\Collection
    {
        Gate::authorize('view', $project);

        return $query($project);
    }

    public function store(Request $request, Project $project, AddProjectMember $action): \Illuminate\Http\Response
    {
        Gate::authorize('add_member', $project);

        /** @var \App\Models\User $user */
        $user = User::findOrFail($request->input('user_id'));
        $action($project, $user);

        return response()->noContent();
    }

    public function destroy(Project $project, User $user, RemoveProjectMember $action): \Illuminate\Http\Response
    {
        Gate::authorize('remove_member', $project);

        $action($project, $user);

        return response()->noContent();
    }
}
