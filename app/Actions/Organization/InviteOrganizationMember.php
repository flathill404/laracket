<?php

namespace App\Actions\Organization;

use App\Enums\OrganizationRole;
use App\Models\Organization;
use App\Models\OrganizationInvitation;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class InviteOrganizationMember
{
    /**
     * @throws ValidationException
     */
    public function __invoke(Organization $organization, string $email, OrganizationRole $role): OrganizationInvitation
    {
        $this->validateUserIsAlreadyMemberOfOrganization($organization, $email);

        $this->validateInvitationAlreadyExists($organization, $email);

        // Create invitation
        $invitation = DB::transaction(function () use ($organization, $email, $role) {
            return OrganizationInvitation::create([
                'organization_id' => $organization->id,
                'email' => $email,
                'role' => $role->value,
                'token' => Str::random(32),
            ]);
        });

        return $invitation;
    }

    /**
     * Check if user is already a member
     *
     * @throws ValidationException
     */
    protected function validateUserIsAlreadyMemberOfOrganization(Organization $organization, string $email)
    {
        $existingUser = User::where('email', $email)->first();
        if ($existingUser && $organization->users()->where('user_id', $existingUser->id)->exists()) {
            throw ValidationException::withMessages([
                'email' => ['This user is already a member of the organization.'],
            ]);
        }
    }

    /**
     * Check if invitation already exists
     *
     * @throws ValidationException
     */
    protected function validateInvitationAlreadyExists(Organization $organization, string $email)
    {
        // Check if invitation already exists
        $existingInvitation = OrganizationInvitation::where('organization_id', $organization->id)
            ->where('email', $email)
            ->first();

        if ($existingInvitation) {
            throw ValidationException::withMessages([
                'email' => ['An invitation has already been sent to this email address.'],
            ]);
        }
    }
}
