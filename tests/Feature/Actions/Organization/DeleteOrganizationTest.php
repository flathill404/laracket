<?php

use App\Actions\Organization\DeleteOrganization;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('deletes an organization', function () {
    $user = User::factory()->create();
    $organization = Organization::factory()->create(['owner_user_id' => $user->id]);
    $action = new DeleteOrganization;

    $action->delete($user, $organization);

    $this->assertModelMissing($organization);
});
