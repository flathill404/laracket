<?php

namespace Tests\Feature\Actions\Organization;

use App\Actions\Organization\InviteOrganizationMember;
use App\Enums\OrganizationRole;
use App\Models\Organization;
use App\Models\OrganizationInvitation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class InviteOrganizationMemberTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_invite_new_member(): void
    {
        $organization = Organization::factory()->create();
        $action = new InviteOrganizationMember;

        $invitation = $action($organization, 'test@example.com', OrganizationRole::Member);

        $this->assertDatabaseHas('organization_invitations', [
            'organization_id' => $organization->id,
            'email' => 'test@example.com',
            'role' => OrganizationRole::Member->value,
        ]);

        $this->assertNotNull($invitation->token);
    }

    public function test_cannot_invite_existing_member(): void
    {
        $organization = Organization::factory()->create();
        $user = User::factory()->create(['email' => 'existing@example.com']);
        $organization->users()->attach($user, ['role' => 'member']);

        $action = new InviteOrganizationMember;

        $this->expectException(ValidationException::class);
        $action($organization, 'existing@example.com', OrganizationRole::Member);
    }

    public function test_cannot_send_duplicate_invitation(): void
    {
        $organization = Organization::factory()->create();

        OrganizationInvitation::create([
            'organization_id' => $organization->id,
            'email' => 'test@example.com',
            'role' => OrganizationRole::Member,
            'token' => 'existing-token',
        ]);

        $action = new InviteOrganizationMember;

        $this->expectException(ValidationException::class);
        $action($organization, 'test@example.com', OrganizationRole::Member);
    }

    public function test_can_invite_different_email_to_same_organization(): void
    {
        $organization = Organization::factory()->create();
        $action = new InviteOrganizationMember;

        $action($organization, 'first@example.com', OrganizationRole::Member);
        $action($organization, 'second@example.com', OrganizationRole::Member);

        $this->assertDatabaseCount('organization_invitations', 2);
    }
}
