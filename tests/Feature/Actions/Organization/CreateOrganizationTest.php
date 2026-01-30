<?php

declare(strict_types=1);

use App\Actions\Organization\CreateOrganization;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Validation\ValidationException;

use function Pest\Laravel\assertDatabaseHas;

uses(LazilyRefreshDatabase::class);

describe('CreateOrganization', function () {
    it('creates an organization', function () {
        $user = User::factory()->create();
        $action = new CreateOrganization;

        $input = [
            'slug' => 'test-organization',
            'name' => 'Test Org Display',
        ];

        $organization = $action($user, $input);

        assertDatabaseHas('organizations', [
            'id' => $organization->id,
            'slug' => 'test-organization',
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
            'slug' => 'invalid-name!',
            'name' => 'Valid Display',
        ]))->toThrow(ValidationException::class);

        expect(fn () => $action($user, [
            'slug' => 'valid-name',
            'name' => str_repeat('a', 101),
        ]))->toThrow(ValidationException::class);
    });
});
