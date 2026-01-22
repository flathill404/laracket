<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Actions\Project\AddProjectMember;
use App\Actions\Project\RemoveProjectMember;
use App\Http\Resources\UserResource;
use App\Models\Project;
use App\Models\User;
use App\Queries\GetProjectMembers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class ProjectMemberController
{
    public function index(Project $project, GetProjectMembers $query): \Illuminate\Http\Resources\Json\AnonymousResourceCollection
    {
        Gate::authorize('view', $project);

        $members = $query($project);

        return UserResource::collection($members);
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
