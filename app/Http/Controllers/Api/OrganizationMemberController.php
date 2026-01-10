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
    /**
     * @return \Illuminate\Database\Eloquent\Collection<int, \App\Models\User>
     */
    public function index(Organization $org, GetOrganizationMembers $query): \Illuminate\Support\Collection
    {
        return $query($org);
    }

    public function store(Request $request, Organization $org, InviteOrganizationMember $action): \Illuminate\Http\Response
    {
        $role = \App\Enums\OrganizationRole::tryFrom($request->string('role')->toString()) ?? \App\Enums\OrganizationRole::Member;
        $action($org, $request->string('email')->toString(), $role);

        return response()->noContent();
    }

    public function update(Request $request, Organization $org, User $user, UpdateOrganizationMemberRole $action): \Illuminate\Http\Response
    {
        $action($org, $user, \App\Enums\OrganizationRole::from($request->string('role')->value()));

        return response()->noContent();
    }

    public function destroy(Organization $org, User $user, RemoveOrganizationMember $action): \Illuminate\Http\Response
    {
        $action($org, $user);

        return response()->noContent();
    }
}
