<?php

namespace Tests\Unit\Actions;

use App\Actions\Fortify\ResetUserPassword;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class ResetUserPasswordTest extends TestCase
{
  use RefreshDatabase;

  public function test_can_reset_password(): void
  {
    $user = User::factory()->create([
      'password' => Hash::make('old-password'),
    ]);
    $action = new ResetUserPassword();
    $newPassword = 'New-Super-Power-Password-123';

    $action->reset($user, [
      'password' => $newPassword,
      'password_confirmation' => $newPassword,
    ]);

    $user->refresh();
    $this->assertTrue(
      Hash::check($newPassword, $user->password),
      'The user password was not reset correctly.'
    );
  }

  public function test_validates_password_rules(): void
  {
    $user = User::factory()->create();
    $action = new ResetUserPassword();

    $this->expectException(ValidationException::class);

    $action->reset($user, [
      'password' => 'short',
      'password_confirmation' => 'mismatch',
    ]);
  }
}
