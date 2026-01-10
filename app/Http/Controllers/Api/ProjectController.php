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
    public function index(Organization $org, GetOrganizationProjects $query)
    {
        return $query($org);
    }

    public function store(Request $request, Organization $org, CreateProject $action)
    {
        $project = $action($org, $request->user(), $request->all());

        return response()->json($project, 201);
    }

    public function show(Project $project, GetProjectDetail $query)
    {
        return $query($project);
    }

    public function update(Request $request, Project $project, UpdateProject $action)
    {
        $project = $action($project, $request->all());

        return response()->json($project);
    }

    public function destroy(Project $project, DeleteProject $action)
    {
        $action($project);

        return response()->noContent();
    }
}
