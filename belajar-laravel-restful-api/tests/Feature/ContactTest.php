<?php

namespace Tests\Feature;

use App\Models\Contact;
use App\Models\User;
use App\Services\ContactService;
use Exception;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

class ContactTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected string $token;

    protected function setUp(): void
    {
        parent::setUp();

        // Buat user dengan factory dan login
        $this->user = User::factory()->create([
            'username' => 'test',
            'password' => bcrypt('test'),
        ]);

        $response = $this->postJson('/api/users/login', [
            'username' => 'test',
            'password' => 'test'
        ]);

        $this->token = $response->json('data.token');
    }

    protected function authHeaders(): array
    {
        return [
            'Authorization' => 'Bearer ' . $this->token,
            'Accept' => 'application/json',
        ];
    }

    public function test_create_contact_successfully()
    {
        $payload = [
            'first_name' => 'Jane',
            'last_name' => 'Doe',
            'email' => 'jane.doe@mail.com',
            'phone' => '082382950271',
        ];

        $response = $this->withHeaders($this->authHeaders())
            ->postJson('/api/contacts', $payload);

        $response->assertStatus(201)->assertJson([
            'data' => [
                'first_name' => 'Jane',
                'last_name' => 'Doe',
                'email' => 'jane.doe@mail.com',
                'phone' => '082382950271',
            ]
        ]);

        $this->assertDatabaseHas('contacts', [
            'first_name' => 'Jane',
            'email' => 'jane.doe@mail.com',
        ]);
    }

    public function test_create_contact_requires_authentication()
    {
        $response = $this->postJson('/api/contacts', [
            'first_name' => 'Jane'
        ]);

        $response->assertStatus(401)->assertJson([
            'message' => 'Unauthenticated.'
        ]);
    }

    public function test_create_contact_validation_errors()
    {
        $response = $this->withHeaders($this->authHeaders())
            ->postJson('/api/contacts', []);

        $response->assertStatus(400)->assertJsonValidationErrors('first_name');
    }

    public function test_show_contact_by_id_successfully()
    {
        $contact = Contact::factory()->create([
            'user_id' => $this->user->id,
        ]);

        $response = $this->withHeaders($this->authHeaders())
            ->getJson('/api/contacts/' . $contact->id);

        $response->assertStatus(200)->assertJson([
            'data' => [
                'id' => $contact->id,
                'first_name' => $contact->first_name,
                'last_name' => $contact->last_name,
                'email' => $contact->email,
                'phone' => $contact->phone,
            ]
        ]);
    }

    public function test_show_contact_not_found()
    {
        $response = $this->withHeaders($this->authHeaders())
            ->getJson('/api/contacts/23432');

        $response->assertStatus(404)->assertJson([
            'errors' => [
                'contact' => ['Contact with specified ID not found.']
            ]
        ]);
    }

    public function test_show_contact_requires_authentication()
    {
        $response = $this->getJson('/api/contacts/1');

        $response->assertStatus(401)
            ->assertJson([
                'message' => 'Unauthenticated.'
            ]);
    }

    public function test_update_contact_successfully()
    {
        $contact = Contact::factory()->create([
            'user_id' => $this->user->id,
            'first_name' => 'Old',
            'last_name' => 'Name',
            'email' => 'old@example.com',
            'phone' => '0811',
        ]);

        $payload = [
            'first_name' => 'Joe',
            'last_name' => 'Blow',
            'email' => 'joe.blow@example.com',
            'phone' => '0899123456'
        ];

        $response = $this->withHeaders($this->authHeaders())
            ->putJson('/api/contacts/' . $contact->id, $payload);

        $response->assertStatus(200)->assertJson([
            'data' => [
                'id' => $contact->id,
                'first_name' => 'Joe',
                'last_name' => 'Blow',
                'email' => 'joe.blow@example.com',
                'phone' => '0899123456'
            ]
        ]);

        $this->assertDatabaseHas('contacts', [
            'id' => $contact->id,
            'first_name' => 'Joe',
        ]);
    }

    public function test_update_contact_validation_errors()
    {
        $contact = Contact::factory()->create([
            'user_id' => $this->user->id,
            'first_name' => 'Old',
            'email' => 'old@example.com',
            'phone' => '123456',
        ]);

        $payload = [
            'first_name' => '', // required
            'email' => 'not-an-email', // invalid
            'phone' => '',
        ];

        $response = $this->withHeaders($this->authHeaders())
            ->putJson('/api/contacts/' . $contact->id, $payload);

        $response->assertStatus(400)
            ->assertJsonValidationErrors(['first_name', 'email']);
    }

    public function test_user_cannot_update_other_users_contact()
    {
        $otherUser = User::factory()->create();
        $contact = Contact::factory()->create([
            'user_id' => $otherUser->id,
        ]);

        $payload = [
            'first_name' => 'Hacker',
            'email' => 'hack@mail.com',
            'phone' => '209832',
        ];

        $response = $this->withHeaders($this->authHeaders())
            ->putJson('/api/contacts/' . $contact->id, $payload);

        $response->assertStatus(404)->assertJson([
            'errors' => [
                'contact' => ['Contact with ID not found.']
            ]
        ]);
    }

    public function test_unauthenticated_user_cannot_update_contact()
    {
        $contact = Contact::factory()->create();

        $response = $this->putJson('/api/contacts/' . $contact->id, [
            'first_name' => 'Nope',
            'email' => 'unauth@mail.com',
            'phone' => '239'
        ]);

        $response->assertStatus(401)->assertJson([
            'message' => 'Unauthenticated.'
        ]);
    }

    public function test_user_can_delete_own_contact_successfully()
    {
        $contact = Contact::factory()->create([
            'user_id' => $this->user->id,
        ]);

        $response = $this->withHeaders($this->authHeaders())
            ->deleteJson("/api/contacts/{$contact->id}");

        $response->assertStatus(200)
            ->assertJson([
                'data' => true,
            ]);

        $this->assertDatabaseMissing('contacts', [
            'id' => $contact->id,
        ]);
    }

    public function test_user_cannot_delete_other_users_contact()
    {
        $otherUser = User::factory()->create();
        $contact = Contact::factory()->create([
            'user_id' => $otherUser->id,
        ]);

        $response = $this->withHeaders($this->authHeaders())
            ->deleteJson("/api/contacts/{$contact->id}");

        $response->assertStatus(404)
            ->assertJson([
                'errors' => [
                    'contact' => ['Contact with specified ID not found.']
                ]
            ]);
    }

    public function test_unauthenticated_user_cannot_delete_contact()
    {
        $contact = Contact::factory()->create();

        $response = $this->deleteJson("/api/contacts/{$contact->id}");

        $response->assertStatus(401)
            ->assertJson([
                'message' => 'Unauthenticated.'
            ]);
    }

    public function test_delete_contact_that_does_not_exist()
    {
        $response = $this->withHeaders($this->authHeaders())
            ->deleteJson("/api/contacts/99999");

        $response->assertStatus(404)
            ->assertJson([
                'errors' => [
                    'contact' => ['Contact with specified ID not found.']
                ]
            ]);
    }

    public function test_delete_contact_server_error_simulation()
    {
        $contact = Contact::factory()->create([
            'user_id' => $this->user->id,
        ]);

        $mockService = Mockery::mock(ContactService::class);
        $mockService->shouldReceive('deleteById')
            ->with($this->user->id, (string) $contact->id)
            ->andThrow(new Exception('Simulated exception'));

        // Bind mock ke Laravel container
        $this->app->instance(ContactService::class, $mockService);

        $response = $this->withHeaders($this->authHeaders())
            ->deleteJson("/api/contacts/{$contact->id}");

        $response->assertStatus(500)
            ->assertJson([
                'errors' => [
                    'server' => ['Unexpected error while updating contact.']
                ]
            ]);
    }

    public function test_user_can_search_contacts_with_pagination()
    {
        // Buat beberapa kontak milik user
        Contact::factory()->count(15)->create([
            'user_id' => $this->user->id,
            'first_name' => 'Jane',
        ]);

        // Buat kontak milik user lain (harus tidak muncul)
        Contact::factory()->count(5)->create([
            'first_name' => 'Jane',
        ]);

        $response = $this->withHeaders($this->authHeaders())
            ->getJson('/api/contacts?name=Jane&page=1&size=10');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'first_name', 'last_name', 'email', 'phone'],
                ],
                'meta' => ['page', 'size', 'total'],
            ]);

        // Pastikan hanya data user yang sedang login yang muncul
        $this->assertCount(10, $response->json('data')); // hanya 10 per page
        $this->assertEquals(15, $response->json('meta.total'));
        $this->assertEquals(1, $response->json('meta.page'));
        $this->assertEquals(10, $response->json('meta.size'));
    }

    public function test_user_can_search_contacts_by_name()
    {
        Contact::factory()->create([
            'user_id' => $this->user->id,
            'first_name' => 'Anna'
        ]);
        Contact::factory()->create([
            'user_id' => $this->user->id,
            'first_name' => 'Bob'
        ]);

        $response = $this->withHeaders($this->authHeaders())
            ->getJson('/api/contacts?name=Ann');

        $response->assertStatus(200);
        $this->assertCount(1, $response->json('data'));
        $this->assertEquals('Anna', $response->json('data.0.first_name'));
    }

    public function test_user_can_search_contacts_by_email()
    {
        Contact::factory()->create([
            'user_id' => $this->user->id,
            'email' => 'unique@mail.com',
        ]);

        $response = $this->withHeaders($this->authHeaders())
            ->getJson('/api/contacts?email=unique@mail.com');

        $response->assertStatus(200);
        $this->assertCount(1, $response->json('data'));
        $this->assertEquals('unique@mail.com', $response->json('data.0.email'));
    }

    public function test_user_can_search_contacts_by_phone()
    {
        Contact::factory()->create([
            'user_id' => $this->user->id,
            'phone' => '089911112222',
        ]);

        $response = $this->withHeaders($this->authHeaders())
            ->getJson('/api/contacts?phone=089911112222');

        $response->assertStatus(200);
        $this->assertCount(1, $response->json('data'));
        $this->assertEquals('089911112222', $response->json('data.0.phone'));
    }

    public function test_pagination_returns_correct_results()
    {
        Contact::factory()->count(25)->create([
            'user_id' => $this->user->id
        ]);

        $response = $this->withHeaders($this->authHeaders())
            ->getJson('/api/contacts?page=2&size=10');

        $response->assertStatus(200);
        $this->assertCount(10, $response->json('data'));
        $this->assertEquals(2, $response->json('meta.page'));
        $this->assertEquals(10, $response->json('meta.size'));
        $this->assertEquals(25, $response->json('meta.total'));
    }

    public function test_user_does_not_see_others_contacts()
    {
        Contact::factory()->create([
            'user_id' => User::factory()->create()->id,
            'first_name' => 'Hacker',
        ]);

        $response = $this->withHeaders($this->authHeaders())
            ->getJson('/api/contacts?name=Hacker');

        $response->assertStatus(200);
        $this->assertCount(0, $response->json('data'));
    }


    public function test_search_contacts_requires_authentication()
    {
        $response = $this->getJson('/api/contacts?name=Jane');

        $response->assertStatus(401)
            ->assertJson([
                'message' => 'Unauthenticated.',
            ]);
    }
}
