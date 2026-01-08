<?php

namespace Tests\Unit\Actions;

use App\Actions\Fortify\UpdateUserPassword;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class UpdateUserPasswordTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_update_password(): void
    {
        $user = User::factory()->make([
            'password' => Hash::make('old-password'),
        ]);
        $this->actingAs($user);
        $action = new UpdateUserPassword;

        $newPassword = 'New-Super-Strong-Password-999';
        $action->update($user, [
            'current_password' => 'old-password',
            'password' => $newPassword,
            'password_confirmation' => $newPassword,
        ]);

        $user->refresh();
        $this->assertTrue(
            Hash::check($newPassword, $user->password),
            'The user password was not updated correctly.'
        );
    }

    public function test_current_password_must_be_correct(): void
    {
        $user = User::factory()->make([
            'password' => Hash::make('old-password'),
        ]);
        $this->actingAs($user);
        $action = new UpdateUserPassword;

        $this->expectException(ValidationException::class);

        $action->update($user, [
            'current_password' => 'wrong-password',
            'password' => 'new-password',
            'password_confirmation' => 'new-password',
        ]);
    }

    public function test_new_password_must_adhere_to_rules(): void
    {
        $user = User::factory()->make();
        $this->actingAs($user);
        $action = new UpdateUserPassword;

        $this->expectException(ValidationException::class);

        $action->update($user, [
            'current_password' => 'password',
            'password' => 'short',
            'password_confirmation' => 'mismatch',
        ]);
    }
}
