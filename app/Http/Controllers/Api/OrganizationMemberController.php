<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Actions\Organization\InviteOrganizationMember;
use App\Actions\Organization\RemoveOrganizationMember;
use App\Actions\Organization\UpdateOrganizationMemberRole;
use App\Enums\OrganizationRole;
use App\Http\Resources\UserResource;
use App\Models\Organization;
use App\Models\User;
use App\Queries\GetOrganizationMembers;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;

class OrganizationMemberController
{
    public function index(Organization $organization, GetOrganizationMembers $query): AnonymousResourceCollection
    {
        Gate::authorize('view', $organization);

        $members = $query($organization);

        return UserResource::collection($members);
    }

    public function store(Request $request, Organization $organization, InviteOrganizationMember $action): Response
    {
        Gate::authorize('invite_member', $organization);

        $role = OrganizationRole::tryFrom($request->string('role')->toString()) ?? OrganizationRole::Member;
        $action($organization, $request->string('email')->toString(), $role);

        return response()->noContent();
    }

    public function update(Request $request, Organization $organization, User $user, UpdateOrganizationMemberRole $action): Response
    {
        Gate::authorize('update_member_role', $organization);

        $action($organization, $user, OrganizationRole::from($request->string('role')->value()));

        return response()->noContent();
    }

    public function destroy(Organization $organization, User $user, RemoveOrganizationMember $action): Response
    {
        Gate::authorize('remove_member', $organization);

        $action($organization, $user);

        return response()->noContent();
    }
}
