<?php

namespace Tests\Feature\Actions\Organization;

use App\Actions\Organization\CreateOrganization;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CreateOrganizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_creates_an_organization(): void
    {
        $user = User::factory()->create();
        $action = new CreateOrganization;

        $data = [
            'name' => 'Test Organization',
            'display_name' => 'Test Org Display',
        ];

        $organization = $action->create($user, $data);

        $this->assertDatabaseHas('organizations', [
            'id' => $organization->id,
            'name' => 'Test Organization',
            'owner_user_id' => $user->id,
        ]);

        $this->assertDatabaseHas('organization_user', [
            'organization_id' => $organization->id,
            'user_id' => $user->id,
            'role' => 'admin',
        ]);
    }
}
