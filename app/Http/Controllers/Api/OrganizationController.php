<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Actions\Organization\CreateOrganization;
use App\Actions\Organization\DeleteOrganization;
use App\Actions\Organization\UpdateOrganization;
use App\Http\Resources\OrganizationResource;
use App\Models\Organization;
use App\Queries\GetMyOrganizations;
use App\Queries\GetOrganizationDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class OrganizationController
{
    public function index(Request $request, GetMyOrganizations $query): \Illuminate\Http\Resources\Json\AnonymousResourceCollection
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

        $organizations = $query($user);

        return OrganizationResource::collection($organizations);
    }

    public function store(Request $request, CreateOrganization $action): \Illuminate\Http\JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();
        /** @var array<string, mixed> $input */
        $input = $request->all();
        $organization = $action($user, $input);

        return response()->json(new OrganizationResource($organization), 201);
    }

    public function show(Organization $organization, GetOrganizationDetail $query): OrganizationResource
    {
        Gate::authorize('view', $organization);

        $organization = $query($organization);

        return new OrganizationResource($organization);
    }

    public function update(Request $request, Organization $organization, UpdateOrganization $action): OrganizationResource
    {
        Gate::authorize('update', $organization);

        /** @var array<string, mixed> $input */
        $input = $request->all();
        $organization = $action($organization, $input);

        return new OrganizationResource($organization);
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
