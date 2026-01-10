<?php

namespace Tests\Feature\Actions\Organization;

use App\Actions\Organization\CreateOrganization;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class CreateOrganizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_creates_an_organization(): void
    {
        $user = User::factory()->create();
        $action = new CreateOrganization;

        $input = [
            'name' => 'TestOrganization',
            'display_name' => 'Test Org Display',
        ];

        $organization = $action->create($user, $input);

        $this->assertDatabaseHas('organizations', [
            'id' => $organization->id,
            'name' => 'TestOrganization',
            'owner_user_id' => $user->id,
        ]);

        $this->assertDatabaseHas('organization_user', [
            'organization_id' => $organization->id,
            'user_id' => $user->id,
            'role' => 'admin',
        ]);
    }

    public function test_it_validates_organization_creation(): void
    {
        $user = User::factory()->create();
        $action = new CreateOrganization;

        $this->assertThrows(function () use ($action, $user) {
            $action->create($user, [
                'name' => 'Invalid Name!',
                'display_name' => 'Valid Display',
            ]);
        }, ValidationException::class);

        $this->assertThrows(function () use ($action, $user) {
            $action->create($user, [
                'name' => 'valid-name',
                'display_name' => str_repeat('a', 101),
            ]);
        }, ValidationException::class);
    }
}
