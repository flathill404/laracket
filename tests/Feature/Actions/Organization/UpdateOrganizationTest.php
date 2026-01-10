<?php

use App\Actions\Organization\UpdateOrganization;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;

use function Pest\Laravel\assertDatabaseHas;

uses(RefreshDatabase::class);

it('updates an organization', function () {
    $user = User::factory()->create();
    $organization = Organization::factory()->create(['owner_user_id' => $user->id]);
    $action = new UpdateOrganization;

    $input = [
        'name' => 'UpdatedOrganizationName',
    ];

    $action($organization, $input);

    assertDatabaseHas('organizations', [
        'id' => $organization->id,
        'name' => 'UpdatedOrganizationName',
    ]);
});

it('validates organization update', function () {
    $user = User::factory()->create();
    $organization = Organization::factory()->create(['owner_user_id' => $user->id]);
    $action = new UpdateOrganization;

    expect(fn () => $action($organization, [
        'name' => 'Invalid Name!',
    ]))->toThrow(ValidationException::class);

    expect(fn () => $action($organization, [
        'display_name' => str_repeat('a', 101),
    ]))->toThrow(ValidationException::class);
});
