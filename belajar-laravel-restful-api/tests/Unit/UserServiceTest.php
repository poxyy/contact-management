<?php

namespace Tests\Unit;

use App\Models\User;
use App\Services\UserService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UserServiceTest extends TestCase
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

    public function test_update_name_and_password()
    {
        $user = User::where('username', 'test')->first();

        $service = new UserService();
        $updated = $service->updateUser($user, [
            'name' => 'New Name',
            'password' => 'newpass123'
        ]);

        $this->assertEquals('New Name', $updated->name);
        $this->assertTrue(Hash::check('newpass123', $updated->password));
    }
}
