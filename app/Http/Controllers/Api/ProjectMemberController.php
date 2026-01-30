<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Actions\Project\AddProjectMember;
use App\Actions\Project\RemoveProjectMember;
use App\Http\Resources\UserResource;
use App\Models\Organization;
use App\Models\Project;
use App\Models\User;
use App\Queries\GetProjectMembers;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;

class ProjectMemberController
{
    public function index(Organization $organization, Project $project, GetProjectMembers $query): AnonymousResourceCollection
    {
        Gate::authorize('view', $project);

        $members = $query($project);

        return UserResource::collection($members);
    }

    public function store(Request $request, Organization $organization, Project $project, AddProjectMember $action): Response
    {
        Gate::authorize('add_member', $project);

        $user = User::findOrFail($request->input('user_id'));
        assert($user instanceof \App\Models\User);

        $action($project, $user);

        return response()->noContent();
    }

    public function destroy(Organization $organization, Project $project, User $user, RemoveProjectMember $action): Response
    {
        Gate::authorize('remove_member', $project);

        $action($project, $user);

        return response()->noContent();
    }
}
