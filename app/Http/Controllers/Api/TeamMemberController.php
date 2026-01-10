<?php

namespace App\Http\Controllers\Api;

use App\Actions\Team\AddTeamMember;
use App\Actions\Team\RemoveTeamMember;
use App\Actions\Team\UpdateTeamMemberRole;
use App\Http\Controllers\Controller;
use App\Models\Team;
use App\Models\User;
use App\Queries\GetTeamMembers;
use Illuminate\Http\Request;

class TeamMemberController extends Controller
{
    /**
     * @return \Illuminate\Database\Eloquent\Collection<int, \App\Models\User>
     */
    public function index(Team $team, GetTeamMembers $query): \Illuminate\Support\Collection
    {
        return $query($team);
    }

    public function store(Request $request, Team $team, AddTeamMember $action): \Illuminate\Http\Response
    {
        /** @var \App\Models\User $user */
        $user = User::findOrFail($request->input('user_id'));
        $action($team, $user);

        return response()->noContent();
    }

    public function update(Request $request, Team $team, User $user, UpdateTeamMemberRole $action): \Illuminate\Http\Response
    {
        $action($team, $user, \App\Enums\TeamRole::from($request->string('role')->value()));

        return response()->noContent();
    }

    public function destroy(Team $team, User $user, RemoveTeamMember $action): \Illuminate\Http\Response
    {
        $action($team, $user);

        return response()->noContent();
    }
}
