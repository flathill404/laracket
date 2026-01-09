<?php

namespace Tests\Feature\Actions\Organization;

use App\Actions\Organization\DeleteOrganization;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DeleteOrganizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_deletes_an_organization(): void
    {
        $user = User::factory()->create();
        $organization = Organization::factory()->create(['owner_user_id' => $user->id]);
        $action = new DeleteOrganization;

        $action->execute($user, $organization);

        $this->assertModelMissing($organization);
    }
}
