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
    public function index(Organization $org, GetOrganizationTeams $query)
    {
        return $query($org);
    }

    public function store(Request $request, Organization $org, CreateTeam $action)
    {
        $team = $action($org, $request->user(), $request->all());

        return response()->json($team, 201);
    }

    public function show(Team $team, GetTeamDetail $query)
    {
        return $query($team);
    }

    public function update(Request $request, Team $team, UpdateTeam $action)
    {
        $team = $action($team, $request->all());

        return response()->json($team);
    }

    public function destroy(Team $team, DeleteTeam $action)
    {
        $action($team);

        return response()->noContent();
    }
}
