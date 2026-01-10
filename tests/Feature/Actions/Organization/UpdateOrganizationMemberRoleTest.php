<?php

use App\Actions\Organization\UpdateOrganizationMemberRole;
use App\Enums\OrganizationRole;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;

use function Pest\Laravel\assertDatabaseHas;

uses(RefreshDatabase::class);

it('updates a member role', function () {
    $organization = Organization::factory()->create();
    $user = User::factory()->create();
    $organization->users()->attach($user, ['role' => OrganizationRole::Member->value]);

    $action = new UpdateOrganizationMemberRole;

    $action($organization, $user, OrganizationRole::Admin);

    assertDatabaseHas('organization_user', [
        'organization_id' => $organization->id,
        'user_id' => $user->id,
        'role' => OrganizationRole::Admin->value,
    ]);
});

it('validates owner role cannot be changed', function () {
    $owner = User::factory()->create();
    $organization = Organization::factory()->create(['owner_user_id' => $owner->id]);
    $organization->users()->attach($owner, ['role' => OrganizationRole::Admin->value]);

    $action = new UpdateOrganizationMemberRole;

    expect(fn () => $action($organization, $owner, OrganizationRole::Member))
        ->toThrow(ValidationException::class);
});

it('validates user is a member', function () {
    $organization = Organization::factory()->create();
    $user = User::factory()->create();

    $action = new UpdateOrganizationMemberRole;

    expect(fn () => $action($organization, $user, OrganizationRole::Admin))
        ->toThrow(ValidationException::class);
});
