<?php

namespace App\Http\Controllers\Api;

use App\Actions\Organization\InviteOrganizationMember;
use App\Actions\Organization\RemoveOrganizationMember;
use App\Actions\Organization\UpdateOrganizationMemberRole;
use App\Http\Controllers\Controller;
use App\Models\Organization;
use App\Models\User;
use App\Queries\GetOrganizationMembers;
use Illuminate\Http\Request;

class OrganizationMemberController extends Controller
{
    public function index(Organization $org, GetOrganizationMembers $query)
    {
        return $query->execute($org);
    }

    public function store(Request $request, Organization $org, InviteOrganizationMember $action)
    {
        $action($org, $request->input('email'));

        return response()->noContent();
    }

    public function update(Request $request, Organization $org, User $user, UpdateOrganizationMemberRole $action)
    {
        $action($org, $user, $request->input('role'));

        return response()->noContent();
    }

    public function destroy(Organization $org, User $user, RemoveOrganizationMember $action)
    {
        $action($org, $user);

        return response()->noContent();
    }
}
