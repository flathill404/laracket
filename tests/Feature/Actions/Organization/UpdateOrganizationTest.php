<?php

namespace Tests\Feature\Actions\Organization;

use App\Actions\Organization\UpdateOrganization;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UpdateOrganizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_updates_an_organization(): void
    {
        $user = User::factory()->create();
        $organization = Organization::factory()->create(['owner_user_id' => $user->id]);
        $action = new UpdateOrganization;

        $data = [
            'name' => 'Updated Organization Name',
        ];

        $action->update($user, $organization, $data);

        $this->assertDatabaseHas('organizations', [
            'id' => $organization->id,
            'name' => 'Updated Organization Name',
        ]);
    }
}
