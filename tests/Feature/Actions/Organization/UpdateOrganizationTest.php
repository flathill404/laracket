<?php

use App\Actions\Organization\UpdateOrganization;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;

uses(RefreshDatabase::class);

it('updates an organization', function () {
    $user = User::factory()->create();
    $organization = Organization::factory()->create(['owner_user_id' => $user->id]);
    $action = new UpdateOrganization;

    $input = [
        'name' => 'UpdatedOrganizationName',
    ];

    $action->update($organization, $input);

    $this->assertDatabaseHas('organizations', [
        'id' => $organization->id,
        'name' => 'UpdatedOrganizationName',
    ]);
});

it('validates organization update', function () {
    $user = User::factory()->create();
    $organization = Organization::factory()->create(['owner_user_id' => $user->id]);
    $action = new UpdateOrganization;

    expect(fn () => $action->update($organization, [
        'name' => 'Invalid Name!',
    ]))->toThrow(ValidationException::class);

    expect(fn () => $action->update($organization, [
        'display_name' => str_repeat('a', 101),
    ]))->toThrow(ValidationException::class);
});
