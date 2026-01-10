<?php

namespace Tests\Feature\Actions\Organization;

use App\Actions\Organization\UpdateOrganization;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class UpdateOrganizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_updates_an_organization(): void
    {
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
    }

    public function test_it_validates_organization_update(): void
    {
        $user = User::factory()->create();
        $organization = Organization::factory()->create(['owner_user_id' => $user->id]);
        $action = new UpdateOrganization;

        $this->assertThrows(function () use ($action, $organization) {
            $action->update($organization, [
                'name' => 'Invalid Name!',
            ]);
        }, ValidationException::class);

        $this->assertThrows(function () use ($action, $organization) {
            $action->update($organization, [
                'display_name' => str_repeat('a', 101),
            ]);
        }, ValidationException::class);
    }
}
