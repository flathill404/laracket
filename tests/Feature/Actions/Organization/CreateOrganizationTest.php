<?php

use App\Actions\Organization\CreateOrganization;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Validation\ValidationException;

use function Pest\Laravel\assertDatabaseHas;

uses(LazilyRefreshDatabase::class);

it('creates an organization', function () {
    $user = User::factory()->create();
    $action = new CreateOrganization;

    $input = [
        'name' => 'TestOrganization',
        'display_name' => 'Test Org Display',
    ];

    $organization = $action($user, $input);

    assertDatabaseHas('organizations', [
        'id' => $organization->id,
        'name' => 'TestOrganization',
        'owner_user_id' => $user->id,
    ]);

    assertDatabaseHas('organization_user', [
        'organization_id' => $organization->id,
        'user_id' => $user->id,
        'role' => 'admin',
    ]);
});

it('validates organization creation', function () {
    $user = User::factory()->create();
    $action = new CreateOrganization;

    expect(fn () => $action($user, [
        'name' => 'Invalid Name!',
        'display_name' => 'Valid Display',
    ]))->toThrow(ValidationException::class);

    expect(fn () => $action($user, [
        'name' => 'valid-name',
        'display_name' => str_repeat('a', 101),
    ]))->toThrow(ValidationException::class);
});
