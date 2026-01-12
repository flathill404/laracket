<?php

namespace App\Http\Controllers\Api;

use App\Actions\Team\CreateTeam;
use App\Actions\Team\DeleteTeam;
use App\Actions\Team\UpdateTeam;
use App\Http\Resources\TeamResource;
use App\Models\Organization;
use App\Models\Team;
use App\Queries\GetOrganizationTeams;
use App\Queries\GetTeamDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class TeamController
{
    public function index(Organization $org, GetOrganizationTeams $query): \Illuminate\Http\Resources\Json\AnonymousResourceCollection
    {
        Gate::authorize('view', $org);

        $teams = $query($org);

        return TeamResource::collection($teams);
    }

    public function store(Request $request, Organization $org, CreateTeam $action): \Illuminate\Http\JsonResponse
    {
        Gate::authorize('create_team', $org);

        /** @var \App\Models\User $user */
        $user = $request->user();
        /** @var array<string, mixed> $input */
        $input = $request->all();
        $team = $action($user, $org, $input);

        return response()->json(new TeamResource($team), 201);
    }

    public function show(Team $team, GetTeamDetail $query): TeamResource
    {
        Gate::authorize('view', $team);

        $team = $query($team);

        return new TeamResource($team);
    }

    public function update(Request $request, Team $team, UpdateTeam $action): TeamResource
    {
        Gate::authorize('update', $team);

        /** @var array<string, mixed> $input */
        $input = $request->all();
        $team = $action($team, $input);

        return new TeamResource($team);
    }

    public function destroy(Request $request, Team $team, DeleteTeam $action): \Illuminate\Http\Response
    {
        Gate::authorize('delete', $team);

        /** @var \App\Models\User $user */
        $user = $request->user();
        $action($user, $team);

        return response()->noContent();
    }
}
