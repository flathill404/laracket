<?php

namespace App\Http\Controllers\Api;

use App\Actions\Organization\CreateOrganization;
use App\Actions\Organization\DeleteOrganization;
use App\Actions\Organization\UpdateOrganizationProfile;
use App\Http\Controllers\Controller;
use App\Models\Organization;
use App\Queries\GetMyOrganizations;
use App\Queries\GetOrganizationDetail;
use Illuminate\Http\Request;

class OrganizationController extends Controller
{
    public function index(Request $request, GetMyOrganizations $query)
    {
        return $query($request->user());
    }

    public function store(Request $request, CreateOrganization $action)
    {
        $organization = $action($request->user(), $request->all());

        return response()->json($organization, 201);
    }

    public function show(Organization $organization, GetOrganizationDetail $query)
    {
        return $query($organization);
    }

    public function update(Request $request, Organization $organization, UpdateOrganizationProfile $action)
    {
        $organization = $action($organization, $request->all());

        return response()->json($organization);
    }

    public function destroy(Organization $organization, DeleteOrganization $action)
    {
        $action($organization);

        return response()->noContent();
    }
}
