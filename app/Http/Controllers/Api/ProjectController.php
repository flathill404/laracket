<?php

namespace App\Http\Controllers\Api;

use App\Actions\Project\CreateProject;
use App\Actions\Project\DeleteProject;
use App\Actions\Project\UpdateProject;
use App\Http\Resources\ProjectResource;
use App\Models\Organization;
use App\Models\Project;
use App\Queries\GetOrganizationProjects;
use App\Queries\GetProjectDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class ProjectController
{
    public function index(Organization $organization, GetOrganizationProjects $query): \Illuminate\Http\Resources\Json\AnonymousResourceCollection
    {
        Gate::authorize('view', $organization);

        $projects = $query($organization);

        return ProjectResource::collection($projects);
    }

    public function store(Request $request, Organization $organization, CreateProject $action): \Illuminate\Http\JsonResponse
    {
        Gate::authorize('create_project', $organization);

        /** @var \App\Models\User $user */
        $user = $request->user();
        /** @var array<string, mixed> $input */
        $input = $request->all();
        $project = $action($user, $organization, $input);

        return response()->json(new ProjectResource($project), 201);
    }

    public function show(Project $project, GetProjectDetail $query): ProjectResource
    {
        Gate::authorize('view', $project);

        $project = $query($project);

        return new ProjectResource($project);
    }

    public function update(Request $request, Project $project, UpdateProject $action): ProjectResource
    {
        Gate::authorize('update', $project);

        /** @var array<string, mixed> $input */
        $input = $request->all();
        $project = $action($project, $input);

        return new ProjectResource($project);
    }

    public function destroy(Request $request, Project $project, DeleteProject $action): \Illuminate\Http\Response
    {
        Gate::authorize('delete', $project);

        /** @var \App\Models\User $user */
        $user = $request->user();
        $action($user, $project);

        return response()->noContent();
    }
}
