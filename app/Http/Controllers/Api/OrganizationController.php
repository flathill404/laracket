<?php

namespace App\Http\Controllers\Api;

use App\Actions\Organization\CreateOrganization;
use App\Actions\Organization\DeleteOrganization;
use App\Actions\Organization\UpdateOrganization;
use App\Http\Controllers\Controller;
use App\Models\Organization;
use App\Queries\GetMyOrganizations;
use App\Queries\GetOrganizationDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class OrganizationController extends Controller
{
    /**
     * @return \Illuminate\Database\Eloquent\Collection<int, \App\Models\Organization>
     */
    public function index(Request $request, GetMyOrganizations $query): \Illuminate\Database\Eloquent\Collection
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

        return $query($user);
    }

    public function store(Request $request, CreateOrganization $action): \Illuminate\Http\JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();
        /** @var array<string, mixed> $input */
        $input = $request->all();
        $organization = $action($user, $input);

        return response()->json($organization, 201);
    }

    public function show(Organization $organization, GetOrganizationDetail $query): Organization
    {
        Gate::authorize('view', $organization);

        return $query($organization);
    }

    public function update(Request $request, Organization $organization, UpdateOrganization $action): \Illuminate\Http\JsonResponse
    {
        Gate::authorize('update', $organization);

        /** @var array<string, mixed> $input */
        $input = $request->all();
        $organization = $action($organization, $input);

        return response()->json($organization);
    }

    public function destroy(Request $request, Organization $organization, DeleteOrganization $action): \Illuminate\Http\Response
    {
        Gate::authorize('delete', $organization);

        /** @var \App\Models\User $user */
        $user = $request->user();
        $action($user, $organization);

        return response()->noContent();
    }
}
