<?php

use App\Actions\Organization\InviteOrganizationMember;
use App\Enums\OrganizationRole;
use App\Models\Organization;
use App\Models\OrganizationInvitation;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Validation\ValidationException;

use function Pest\Laravel\assertDatabaseCount;
use function Pest\Laravel\assertDatabaseHas;

uses(LazilyRefreshDatabase::class);

it('invites a new member', function () {
    $organization = Organization::factory()->create();
    $action = new InviteOrganizationMember;

    $invitation = $action($organization, 'test@example.com', OrganizationRole::Member);

    assertDatabaseHas('organization_invitations', [
        'organization_id' => $organization->id,
        'email' => 'test@example.com',
        'role' => OrganizationRole::Member->value,
    ]);

    expect($invitation->token)->not->toBeNull();
});

it('validates existing member', function () {
    $organization = Organization::factory()->create();
    $user = User::factory()->create(['email' => 'existing@example.com']);
    $organization->users()->attach($user, ['role' => 'member']);

    $action = new InviteOrganizationMember;

    expect(fn() => $action($organization, 'existing@example.com', OrganizationRole::Member))
        ->toThrow(ValidationException::class);
});

it('validates duplicate invitation', function () {
    $organization = Organization::factory()->create();

    OrganizationInvitation::create([
        'organization_id' => $organization->id,
        'email' => 'test@example.com',
        'role' => OrganizationRole::Member,
        'token' => 'existing-token',
    ]);

    $action = new InviteOrganizationMember;

    expect(fn() => $action($organization, 'test@example.com', OrganizationRole::Member))
        ->toThrow(ValidationException::class);
});

it('invites different emails to the same organization', function () {
    $organization = Organization::factory()->create();
    $action = new InviteOrganizationMember;

    $action($organization, 'first@example.com', OrganizationRole::Member);
    $action($organization, 'second@example.com', OrganizationRole::Member);

    assertDatabaseCount('organization_invitations', 2);
});
