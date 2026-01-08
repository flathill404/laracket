<?php

namespace Tests\Unit\Actions;

use App\Actions\Fortify\UpdateUserProfileInformation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class UpdateUserProfileInformationTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_update_profile_information(): void
    {
        $user = User::factory()->make([
            'name' => 'Old Name',
            'email' => 'old@example.com',
        ]);

        $action = new UpdateUserProfileInformation;

        $action->update($user, [
            'name' => 'New Name',
            'email' => 'new@example.com',
        ]);
        $user->refresh();

        $this->assertEquals('New Name', $user->name);
        $this->assertEquals('new@example.com', $user->email);
    }

    public function test_email_must_be_unique(): void
    {
        User::factory()->create(['email' => 'taken@example.com']);
        $user = User::factory()->make(['email' => 'original@example.com']);
        $action = new UpdateUserProfileInformation;

        $this->expectException(ValidationException::class);

        $action->update($user, [
            'name' => 'New Name',
            'email' => 'taken@example.com',
        ]);
    }

    public function test_can_update_name_with_same_email(): void
    {
        $user = User::factory()->make(['email' => 'me@example.com']);
        $action = new UpdateUserProfileInformation;

        $action->update($user, [
            'name' => 'New Name',
            'email' => 'me@example.com',
        ]);

        $user->refresh();
        $this->assertEquals('New Name', $user->name);
    }
}
