<?php

namespace Tests\Feature\Api;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class UserAvatarControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_update_avatar_api(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson('/api/user/avatar', [
            'avatar' => 'data:image/png;base64,'.base64_encode('fake-image-content'),
        ]);

        $response->assertOk();
        $response->assertJsonStructure([
            'data' => [
                'id',
                'name',
                'avatar_url',
            ],
        ]);
        
        $this->assertNotNull($user->refresh()->avatar_path);
    }

    public function test_validation_error_api(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson('/api/user/avatar', [
            'avatar' => 'invalid-data',
        ]);

        $response->assertStatus(422);
    }
}
