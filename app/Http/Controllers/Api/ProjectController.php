<?php

namespace App\Http\Controllers\Api;

use App\Actions\Project\CreateProject;
use App\Actions\Project\DeleteProject;
use App\Actions\Project\UpdateProject;
use App\Http\Controllers\Controller;
use App\Models\Organization;
use App\Models\Project;
use App\Queries\GetOrganizationProjects;
use App\Queries\GetProjectDetail;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    /**
     * @return \Illuminate\Database\Eloquent\Collection<int, \App\Models\Project>
     */
    public function index(Organization $org, GetOrganizationProjects $query): \Illuminate\Database\Eloquent\Collection
    {
        return $query($org);
    }

    public function store(Request $request, Organization $org, CreateProject $action): \Illuminate\Http\JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();
        /** @var array<string, mixed> $input */
        $input = $request->all();
        $project = $action($user, $org, $input);

        return response()->json($project, 201);
    }

    public function show(Project $project, GetProjectDetail $query): Project
    {
        return $query($project);
    }

    public function update(Request $request, Project $project, UpdateProject $action): \Illuminate\Http\JsonResponse
    {
        /** @var array<string, mixed> $input */
        $input = $request->all();
        $project = $action($project, $input);

        return response()->json($project);
    }

    public function destroy(Request $request, Project $project, DeleteProject $action): \Illuminate\Http\Response
    {
        /** @var \App\Models\User $user */
        $user = $request->user();
        $action($user, $project);

        return response()->noContent();
    }
}
