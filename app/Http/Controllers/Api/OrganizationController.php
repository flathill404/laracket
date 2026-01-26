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
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;

class OrganizationController
{
    public function index(Request $request, GetMyOrganizations $query): AnonymousResourceCollection
    {
        $user = $request->user();
        assert($user instanceof \App\Models\User);

        $organizations = $query($user);

        return OrganizationResource::collection($organizations);
    }

    public function store(Request $request, CreateOrganization $action): JsonResponse
    {
        $user = $request->user();
        assert($user instanceof \App\Models\User);

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

    public function update(Request $request, Organization $organization, UpdateOrganization $action, GetOrganizationDetail $query): OrganizationResource
    {
        Gate::authorize('update', $organization);

        /** @var array<string, mixed> $input */
        $input = $request->all();
        $action($organization, $input);

        $updatedOrganization = $query($organization);

        return new OrganizationResource($updatedOrganization);
    }

    public function destroy(Request $request, Organization $organization, DeleteOrganization $action): Response
    {
        Gate::authorize('delete', $organization);

        $user = $request->user();
        assert($user instanceof \App\Models\User);

        $action($user, $organization);

        return response()->noContent();
    }
}
