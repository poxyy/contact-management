<?php

namespace Tests\Unit;

use App\Models\User;
use App\Services\AuthService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AuthServiceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        User::factory()->create([
            'username' => 'test',
            'password' => 'password',
            'name' => 'test',
        ]);
    }

    public function test_logout_user_deletes_token()
    {
        $user = User::where('username', 'test')->first();

        Sanctum::actingAs($user);

        $service = new AuthService();
        $service->logoutUser($user);

        $user->refresh();

        $this->assertEquals(0, $user->tokens()->count());

        $this->assertCount(0, $user->tokens);
    }
}
