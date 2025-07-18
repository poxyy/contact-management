<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected string $token;

    protected function setUp(): void
    {
        parent::setUp();

        User::factory()->create([
            'username' => 'test',
            'password' => 'password',
            'name' => 'test',
        ]);

        // Login dan ambil token dari user 'test'
        $response = $this->postJson('/api/users/login', [
            'username' => 'test',
            'password' => 'password',
        ]);

        $this->token = $response->json('data.token');
        
        $this->assertNotNull($this->token, 'Login failed, token is null');
    }

    public function test_register_user_success()
    {
        $payload = [
            'username' => 'john',
            'password' => 'SecretPass123!',
            'name' => 'John Doe',
        ];

        $response = $this->postJson('/api/users', $payload);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'username',
                    'name',
                ]
            ]);

        $this->assertDatabaseHas('users', [
            'username' => $payload['username'],
            'name' => $payload['name'],
        ]);
    }

    public function test_register_user_validation_error()
    {
        $payload = [
            'username' => '',
            'password' => '',
        ];

        $response = $this->postJson('/api/users', $payload);

        $response->assertStatus(400)
            ->assertJsonStructure([
                'errors' => [
                    'username',
                    'password',
                    'name',
                ]
            ]);
    }

    public function test_register_user_duplicate_username()
    {
        User::factory()->create([
            'username' => 'test2',
        ]);

        $payload = [
            'username' => 'test',
            'password' => 'SecretPass123!',
            'name' => 'Another Test',
        ];

        $response = $this->postJson('/api/users', $payload);

        $response->assertStatus(409)
            ->assertJson([
                'errors' => [
                    'username' => ['Username already exists.'],
                ]
            ]);
    }

    public function test_register_user_race_condition()
    {
        $payload = [
            'username' => 'sameuser',
            'password' => 'SecretPass123!',
            'name' => 'User A',
        ];

        // Simulasi request paralel
        $responses[] = $this->postJson('/api/users', $payload);
        $responses[] = $this->postJson('/api/users', $payload);

        $statusCodes = array_map(fn($res) => $res->getStatusCode(), $responses);

        // Setidaknya satu berhasil, satu gagal (409 / 400)
        $this->assertContains(201, $statusCodes);
        $this->assertTrue(in_array(409, $statusCodes) || in_array(400, $statusCodes));

        // Hanya satu yang berhasil masuk database
        $this->assertEquals(1, User::query()->where('username', 'sameuser')->count());
    }

    public function test_user_can_login_with_valid_credentials_and_receive_token()
    {
        $user = User::factory()->create([
            'username' => 'test2',
            'password' => 'password',
            'name' => 'Test User'
        ]);
        // Act
        $response = $this->postJson('/api/users/login', [
            'username' => 'test2',
            'password' => 'password',
        ]);

        // Assert: status dan struktur
        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => ['id', 'username', 'name', 'token']
            ]);

        // Assert: nilai detail
        $data = $response->json('data');
        $this->assertEquals('test2', $data['username']);
        $this->assertNotEmpty($data['token']);

        // Assert: token disimpan di DB oleh Sanctum
        $this->assertDatabaseHas('personal_access_tokens', [
            'tokenable_id' => $user->id,
            'tokenable_type' => User::class,
        ]);
    }

    public function test_user_cannot_login_with_invalid_username()
    {
        $response = $this->postJson('/api/users/login', [
            'username' => 'invalid',
            'password' => 'whatever',
        ]);

        $response->assertStatus(401)
            ->assertJsonFragment([
                'credentials' => ['Invalid username or password.'],
            ]);
    }

    public function test_user_cannot_login_with_wrong_password()
    {
        User::factory()->create([
            'username' => 'test2',
            'password' => 'password',
            'name' => 'test',
        ]);

        $response = $this->postJson('/api/users/login', [
            'username' => 'test2',
            'password' => 'wrong-password',
        ]);

        $response->assertStatus(401)
            ->assertJsonFragment([
                'credentials' => ['Invalid username or password.'],
            ]);
    }


    public function test_user_login_validation_fails_on_empty_input()
    {
        $response = $this->postJson('/api/users/login', []);

        $response->assertStatus(400)
            ->assertJsonValidationErrors(['username', 'password']);
    }

    public function test_user_login_validation_fails_on_short_password()
    {
        $response = $this->postJson('/api/users/login', [
            'username' => 'a',
            'password' => '12',
        ]);

        $response->assertStatus(400)
            ->assertJsonValidationErrors(['username', 'password']);
    }

    public function test_get_current_user_success()
    {
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->getJson('/api/users/current');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => ['id', 'username', 'name']
            ])
            ->assertJson([
                'data' => [
                    'username' => 'test',
                    'name' => 'test',
                ]
            ]);
    }

    public function test_get_current_user_unauthorized_with_invalid_token()
    {
        $response = $this->withHeader('Authorization', 'Bearer invalid-token')
            ->getJson('/api/users/current');

        $response->assertStatus(401)
            ->assertJson([
                'message' => 'Unauthenticated.'
            ]);
    }

    public function test_update_current_user_name_successfully()
    {
        // Lakukan PATCH
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->patchJson('/api/users/current', [
                'name' => 'Updated Name'
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'username' => 'test',
                    'name' => 'Updated Name'
                ]
            ]);

        // Verifikasi di DB
        $this->assertDatabaseHas('users', [
            'username' => 'test',
            'name' => 'Updated Name'
        ]);
    }

    public function test_update_current_user_password_successfully()
    {
        // Update password
        $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->patchJson('/api/users/current', [
                'password' => 'NewPass123'
            ])
            ->assertStatus(200);

        // Coba login pakai password baru
        $newLogin = $this->postJson('/api/users/login', [
            'username' => 'test',
            'password' => 'NewPass123',
        ]);

        $newLogin->assertStatus(200)
            ->assertJsonStructure([
                'data' => ['id', 'username', 'name', 'token']
            ]);
    }

    public function test_update_current_user_requires_authentication()
    {
        $this->patchJson('/api/users/current', [
            'name' => 'Hacker'
        ])->assertStatus(401)
            ->assertJson([
                'message' => 'Unauthenticated.'
            ]);
    }

    public function test_update_current_user_with_invalid_data()
    {
        // Kirim invalid data (name terlalu pendek, password juga)
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->patchJson('/api/users/current', [
                'name' => 'A',
                'password' => '12'
            ]);

        $response->assertStatus(400)
            ->assertJsonValidationErrors(['name', 'password']);
    }

    public function test_logout_successfully()
    {
        // Panggil logout
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->deleteJson('/api/users/logout');

        $response->assertStatus(200)
            ->assertJson([
                'data' => true
            ]);

        // Token harus terhapus dari DB
        $user = User::where('username', 'test')->first();
        $this->assertDatabaseMissing('personal_access_tokens', [
            'tokenable_id' => $user->id,
            'tokenable_type' => User::class,
        ]);
    }

    public function test_logout_with_invalid_token_should_fail()
    {
        $response = $this->withHeader('Authorization', 'Bearer invalid-token')
            ->deleteJson('/api/users/logout');

        $response->assertStatus(401)
            ->assertJson([
                'message' => 'Unauthenticated.'
            ]);
    }
}
