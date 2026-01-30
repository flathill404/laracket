<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Actions\Team\CreateTeam;
use App\Actions\Team\DeleteTeam;
use App\Actions\Team\UpdateTeam;
use App\Http\Resources\TeamResource;
use App\Models\Organization;
use App\Models\Team;
use App\Queries\GetOrganizationTeams;
use App\Queries\GetTeamDetail;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;

class TeamController
{
    public function index(Organization $organization, GetOrganizationTeams $query): AnonymousResourceCollection
    {
        Gate::authorize('view', $organization);

        $teams = $query($organization);

        return TeamResource::collection($teams);
    }

    public function store(Request $request, Organization $organization, CreateTeam $action): JsonResponse
    {
        Gate::authorize('create_team', $organization);

        $user = $request->user();
        assert($user instanceof \App\Models\User);

        /** @var array<string, mixed> $input */
        $input = $request->all();
        $team = $action($user, $organization, $input);

        return response()->json(new TeamResource($team), 201);
    }

    public function show(Organization $organization, Team $team, GetTeamDetail $query): TeamResource
    {
        Gate::authorize('view', $team);

        $team = $query($team);

        return new TeamResource($team);
    }

    public function update(Request $request, Organization $organization, Team $team, UpdateTeam $action, GetTeamDetail $query): TeamResource
    {
        Gate::authorize('update', $team);

        /** @var array<string, mixed> $input */
        $input = $request->all();
        $action($team, $input);

        $updatedTeam = $query($team);

        return new TeamResource($updatedTeam);
    }

    public function destroy(Request $request, Organization $organization, Team $team, DeleteTeam $action): Response
    {
        Gate::authorize('delete', $team);

        $user = $request->user();
        assert($user instanceof \App\Models\User);

        $action($user, $team);

        return response()->noContent();
    }
}
