<?php

namespace Tests\Feature\Actions\Fortify;

use App\Actions\Fortify\CreateNewUser;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class CreateNewUserTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_new_user(): void
    {
        $action = $this->app->make(CreateNewUser::class);
        $input = [
            'name' => 'Power Chan',
            'email' => 'power@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        $user = $action->create($input);

        $this->assertInstanceOf(User::class, $user);
        $this->assertEquals('Power Chan', $user->name);
        $this->assertEquals('power@example.com', $user->email);
        $this->assertTrue(Hash::check('password123', $user->password));
        $this->assertDatabaseHas('users', [
            'email' => 'power@example.com',
        ]);
    }

    public function test_validates_password_confirmation(): void
    {
        $action = $this->app->make(CreateNewUser::class);
        $input = [
            'name' => 'Denji',
            'email' => 'denji@example.com',
            'password' => 'password123',
            'password_confirmation' => 'wrong-password',
        ];

        $this->expectException(ValidationException::class);

        $action->create($input);
    }

    public function test_validates_password_rules(): void
    {
        $action = $this->app->make(CreateNewUser::class);
        $input = [
            'name' => 'Aki',
            'email' => 'aki@example.com',
            'password' => 'short',
            'password_confirmation' => 'short',
        ];

        $this->expectException(ValidationException::class);

        $action->create($input);
    }
}
