<?php

namespace App\Http\Controllers\Api;

use App\Actions\Team\CreateTeam;
use App\Actions\Team\DeleteTeam;
use App\Actions\Team\UpdateTeam;
use App\Http\Controllers\Controller;
use App\Models\Organization;
use App\Models\Team;
use App\Queries\GetOrganizationTeams;
use App\Queries\GetTeamDetail;
use Illuminate\Http\Request;

class TeamController extends Controller
{
    /**
     * @return \Illuminate\Database\Eloquent\Collection<int, \App\Models\Team>
     */
    public function index(Organization $org, GetOrganizationTeams $query): \Illuminate\Database\Eloquent\Collection
    {
        return $query($org);
    }

    public function store(Request $request, Organization $org, CreateTeam $action): \Illuminate\Http\JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();
        /** @var array<string, mixed> $input */
        $input = $request->all();
        $team = $action($user, $org, $input);

        return response()->json($team, 201);
    }

    public function show(Team $team, GetTeamDetail $query): Team
    {
        return $query($team);
    }

    public function update(Request $request, Team $team, UpdateTeam $action): \Illuminate\Http\JsonResponse
    {
        /** @var array<string, mixed> $input */
        $input = $request->all();
        $team = $action($team, $input);

        return response()->json($team);
    }

    public function destroy(Request $request, Team $team, DeleteTeam $action): \Illuminate\Http\Response
    {
        /** @var \App\Models\User $user */
        $user = $request->user();
        $action($user, $team);

        return response()->noContent();
    }
}
