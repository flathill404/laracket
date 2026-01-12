<?php

namespace App\Http\Controllers\Api;

use App\Actions\Organization\InviteOrganizationMember;
use App\Actions\Organization\RemoveOrganizationMember;
use App\Actions\Organization\UpdateOrganizationMemberRole;
use App\Http\Resources\UserResource;
use App\Models\Organization;
use App\Models\User;
use App\Queries\GetOrganizationMembers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class OrganizationMemberController
{
    public function index(Organization $organization, GetOrganizationMembers $query): \Illuminate\Http\Resources\Json\AnonymousResourceCollection
    {
        Gate::authorize('view', $organization);

        $members = $query($organization);

        return UserResource::collection($members);
    }

    public function store(Request $request, Organization $organization, InviteOrganizationMember $action): \Illuminate\Http\Response
    {
        Gate::authorize('invite_member', $organization);

        $role = \App\Enums\OrganizationRole::tryFrom($request->string('role')->toString()) ?? \App\Enums\OrganizationRole::Member;
        $action($organization, $request->string('email')->toString(), $role);

        return response()->noContent();
    }

    public function update(Request $request, Organization $organization, User $user, UpdateOrganizationMemberRole $action): \Illuminate\Http\Response
    {
        Gate::authorize('update_member_role', $organization);

        $action($organization, $user, \App\Enums\OrganizationRole::from($request->string('role')->value()));

        return response()->noContent();
    }

    public function destroy(Organization $organization, User $user, RemoveOrganizationMember $action): \Illuminate\Http\Response
    {
        Gate::authorize('remove_member', $organization);

        $action($organization, $user);

        return response()->noContent();
    }
}
