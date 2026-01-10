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
    public function index(Team $team, GetTeamMembers $query)
    {
        return $query($team);
    }

    public function store(Request $request, Team $team, AddTeamMember $action)
    {
        $action($team, User::find($request->input('user_id')));

        return response()->noContent();
    }

    public function update(Request $request, Team $team, User $user, UpdateTeamMemberRole $action)
    {
        $action($team, $user, $request->input('role'));

        return response()->noContent();
    }

    public function destroy(Team $team, User $user, RemoveTeamMember $action)
    {
        $action($team, $user);

        return response()->noContent();
    }
}
