<?php

declare(strict_types=1);

use App\Actions\Organization\RemoveOrganizationMember;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Validation\ValidationException;

use function Pest\Laravel\assertDatabaseMissing;

uses(LazilyRefreshDatabase::class);

it('removes a member from organization', function () {
    $organization = Organization::factory()->create();
    $user = User::factory()->create();
    $organization->users()->attach($user, ['role' => 'member']);

    $action = new RemoveOrganizationMember;

    $action($organization, $user);

    assertDatabaseMissing('organization_user', [
        'organization_id' => $organization->id,
        'user_id' => $user->id,
    ]);
});

it('validates owner cannot be removed', function () {
    $owner = User::factory()->create();
    $organization = Organization::factory()->create(['owner_user_id' => $owner->id]);

    // Owner is usually implicitly a member or explicitly attached depending on logic,
    // but the validation check is on the ID equality.
    // Ensure owner is attached for completeness if needed, but not strictly required for the validation check itself to fail.
    $organization->users()->attach($owner, ['role' => 'admin']);

    $action = new RemoveOrganizationMember;

    expect(fn () => $action($organization, $owner))
        ->toThrow(ValidationException::class);
});
