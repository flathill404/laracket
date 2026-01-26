<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Actions\Project\CreateProject;
use App\Actions\Project\DeleteProject;
use App\Actions\Project\UpdateProject;
use App\Http\Resources\ProjectResource;
use App\Models\Organization;
use App\Models\Project;
use App\Queries\GetOrganizationProjects;
use App\Queries\GetProjectDetail;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;

class ProjectController
{
    public function index(Organization $organization, GetOrganizationProjects $query): AnonymousResourceCollection
    {
        Gate::authorize('view', $organization);

        $projects = $query($organization);

        return ProjectResource::collection($projects);
    }

    public function store(Request $request, Organization $organization, CreateProject $action): JsonResponse
    {
        Gate::authorize('create_project', $organization);

        $user = $request->user();
        assert($user instanceof \App\Models\User);

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

    public function update(Request $request, Project $project, UpdateProject $action, GetProjectDetail $query): ProjectResource
    {
        Gate::authorize('update', $project);

        /** @var array<string, mixed> $input */
        $input = $request->all();
        $action($project, $input);

        $updatedProject = $query($project);

        return new ProjectResource($updatedProject);
    }

    public function destroy(Request $request, Project $project, DeleteProject $action): Response
    {
        Gate::authorize('delete', $project);

        $user = $request->user();
        assert($user instanceof \App\Models\User);

        $action($user, $project);

        return response()->noContent();
    }
}
