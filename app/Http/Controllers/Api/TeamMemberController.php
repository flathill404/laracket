<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Actions\Team\AddTeamMember;
use App\Actions\Team\RemoveTeamMember;
use App\Actions\Team\UpdateTeamMemberRole;
use App\Enums\TeamRole;
use App\Http\Resources\UserResource;
use App\Models\Team;
use App\Models\User;
use App\Queries\GetTeamMembers;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;

class TeamMemberController
{
    public function index(Team $team, GetTeamMembers $query): AnonymousResourceCollection
    {
        Gate::authorize('view', $team);

        $members = $query($team);

        return UserResource::collection($members);
    }

    public function store(Request $request, Team $team, AddTeamMember $action): Response
    {
        Gate::authorize('add_member', $team);

        /** @var \App\Models\User $user */
        $user = User::findOrFail($request->input('user_id'));
        $action($team, $user);

        return response()->noContent();
    }

    public function update(Request $request, Team $team, User $user, UpdateTeamMemberRole $action): Response
    {
        Gate::authorize('update_member_role', $team);

        $action($team, $user, TeamRole::from($request->string('role')->value()));

        return response()->noContent();
    }

    public function destroy(Team $team, User $user, RemoveTeamMember $action): Response
    {
        Gate::authorize('remove_member', $team);

        $action($team, $user);

        return response()->noContent();
    }
}
