<?php

namespace Tests\Feature;

use App\Models\Address;
use App\Models\Contact;
use App\Models\User;
use App\Services\AddressService;
use Exception;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

class AddressTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected array $headers;


    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $token = $this->user->createToken('auth')->plainTextToken;
        $this->headers = [
            'Authorization' => 'Bearer ' . $token,
        ];
    }

    public function test_user_can_create_address()
    {
        $contact = Contact::factory()->create([
            'user_id' => $this->user->id
        ]);

        $payload = [
            'street' => 'Jl. Merdeka',
            'city' => 'Bandung',
            'province' => 'Jawa Barat',
            'country' => 'Indonesia',
            'postal_code' => '40123',
        ];

        $response = $this->withHeaders($this->headers)
            ->postJson("/api/contacts/{$contact->id}/addresses", $payload);

        $response->assertCreated()
            ->assertJsonPath('data.street', $payload['street']);
    }

    public function test_address_creation_requires_country()
    {
        $contact = Contact::factory()->create([
            'user_id' => $this->user->id
        ]);

        $response = $this->withHeaders($this->headers)
            ->postJson("/api/contacts/{$contact->id}/addresses", []);

        $response->assertStatus(400)
            ->assertJsonValidationErrors(['country']);
    }

    public function test_unauthenticated_user_cannot_create_address()
    {
        $contact = Contact::factory()->create();

        $response = $this->postJson("/api/contacts/{$contact->id}/addresses", [
            'country' => 'Indonesia',
        ]);

        $response->assertUnauthorized()
            ->assertJsonPath('message', 'Unauthenticated.');
    }

    public function test_address_creation_returns_404_if_contact_not_found()
    {
        $response = $this->withHeaders($this->headers)
            ->postJson("/api/contacts/99999/addresses", [
                'country' => 'Indonesia',
            ]);

        $response->assertNotFound()
            ->assertJsonPath('errors.contact.0', 'Contact with specified ID not found');
    }

    public function test_address_creation_returns_500_on_server_error()
    {
        $contact = Contact::factory()->create([
            'user_id' => $this->user->id
        ]);

        // Simulasi error (misal mocking DB atau binding Service gagal)
        // Di sini kita buat cara kasar dengan force throw error:
        $this->mock(AddressService::class, function ($mock) {
            $mock->shouldReceive('createAddress')->andThrow(new Exception('Simulated error'));
        });

        $response = $this->withHeaders($this->headers)
            ->postJson("/api/contacts/{$contact->id}/addresses", [
                'country' => 'Indonesia',
            ]);

        $response->assertStatus(500)
            ->assertJsonPath('errors.server.0', 'Unexpected error while creating address');
    }

    public function test_user_can_get_list_of_addresses_for_contact()
    {
        $contact = Contact::factory()->create([
            'user_id' => $this->user->id,
        ]);

        $addresses = Address::factory()->count(2)->create([
            'contact_id' => $contact->id,
        ]);

        $response = $this->withHeaders($this->headers)
            ->getJson("/api/contacts/{$contact->id}/addresses");

        $response->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('data.0.id', $addresses[0]->id)
            ->assertJsonPath('data.1.id', $addresses[1]->id);
    }

    public function test_unauthenticated_user_cannot_access_addresses()
    {
        $contact = Contact::factory()->create();

        $response = $this->getJson("/api/contacts/{$contact->id}/addresses");

        $response->assertUnauthorized()
            ->assertJson([
                'message' => 'Unauthenticated.'
            ]);
    }

    public function test_user_cannot_access_other_users_contact_addresses()
    {
        $contact = Contact::factory()->create(); // dimiliki oleh user lain

        $response = $this->withHeaders($this->headers)
            ->getJson("/api/contacts/{$contact->id}/addresses");

        $response->assertStatus(404)
            ->assertJsonPath('errors.contact.0', 'Contact not found');
    }

    public function test_server_error_when_fetching_addresses()
    {
        $contact = Contact::factory()->create([
            'user_id' => $this->user->id,
        ]);

        // Simulasi error service
        $mock = Mockery::mock(AddressService::class);
        $mock->shouldReceive('getAddressesByContact')
            ->with($this->user->id, $contact->id)
            ->andThrow(new \Exception('Simulated failure'));

        $this->app->instance(AddressService::class, $mock);

        $response = $this->withHeaders($this->headers)
            ->getJson("/api/contacts/{$contact->id}/addresses");

        $response->assertStatus(500)
            ->assertJsonPath('errors.server.0', 'An unexpected error occurred');
    }

    public function test_user_can_view_single_address()
    {
        $contact = Contact::factory()->create([
            'user_id' => $this->user->id,
        ]);

        $address = Address::factory()->create([
            'contact_id' => $contact->id,
            'street' => 'Jl. Kenangan',
            'city' => 'Surabaya',
            'province' => 'Jawa Timur',
            'country' => 'Indonesia',
            'postal_code' => '60234',
        ]);

        $response = $this->withHeaders($this->headers)
            ->getJson("/api/contacts/{$contact->id}/addresses/{$address->id}");

        $response->assertOk()
            ->assertJson([
                'data' => [
                    'id' => $address->id,
                    'street' => 'Jl. Kenangan',
                    'city' => 'Surabaya',
                    'province' => 'Jawa Timur',
                    'country' => 'Indonesia',
                    'postal_code' => '60234',
                ]
            ]);
    }

    public function test_user_cannot_view_other_users_address()
    {
        $otherContact = Contact::factory()->create(); // milik user lain
        $address = Address::factory()->create([
            'contact_id' => $otherContact->id,
        ]);

        $response = $this->withHeaders($this->headers)
            ->getJson("/api/contacts/{$otherContact->id}/addresses/{$address->id}");

        $response->assertStatus(404)
            ->assertJson([
                'errors' => [
                    'address' => ['Address with specified ID not found']
                ]
            ]);
    }

    public function test_viewing_nonexistent_address_returns_404()
    {
        $contact = Contact::factory()->create([
            'user_id' => $this->user->id,
        ]);

        $response = $this->withHeaders($this->headers)
            ->getJson("/api/contacts/{$contact->id}/addresses/99999");

        $response->assertStatus(404)
            ->assertJson([
                'errors' => [
                    'address' => ['Address with specified ID not found']
                ]
            ]);
    }

    public function test_unauthenticated_user_cannot_view_address()
    {
        $contact = Contact::factory()->create();
        $address = Address::factory()->create([
            'contact_id' => $contact->id,
        ]);

        $response = $this->getJson("/api/contacts/{$contact->id}/addresses/{$address->id}");

        $response->assertStatus(401)
            ->assertJson([
                'message' => 'Unauthenticated.'
            ]);
    }

    public function test_server_error_when_viewing_address()
    {
        $contact = Contact::factory()->create([
            'user_id' => $this->user->id,
        ]);

        $address = Address::factory()->create([
            'contact_id' => $contact->id,
        ]);

        $mockService = Mockery::mock(AddressService::class);
        $mockService->shouldReceive('getAddressById')
            ->with($this->user->id, (string) $contact->id, (string) $address->id)
            ->andThrow(new \Exception('Simulated error'));

        $this->app->instance(AddressService::class, $mockService);

        $response = $this->withHeaders($this->headers)
            ->getJson("/api/contacts/{$contact->id}/addresses/{$address->id}");

        $response->assertStatus(500)
            ->assertJson([
                'errors' => [
                    'server' => ['Unexpected error while retrieving address']
                ]
            ]);
    }

    public function test_user_can_update_address()
    {
        $contact = Contact::factory()->create([
            'user_id' => $this->user->id
        ]);

        $address = Address::factory()->create([
            'contact_id' => $contact->id,
            'street' => 'Old Street',
        ]);

        $payload = [
            'street' => 'Jl. Baru No. 99',
            'city' => 'Surabaya',
            'province' => 'Jawa Timur',
            'country' => 'Indonesia',
            'postal_code' => '60234',
        ];

        $response = $this->withHeaders($this->headers)
            ->putJson("/api/contacts/{$contact->id}/addresses/{$address->id}", $payload);

        $response->assertOk()
            ->assertJsonPath('data.id', $address->id)
            ->assertJsonPath('data.street', 'Jl. Baru No. 99');

        $this->assertDatabaseHas('addresses', [
            'id' => $address->id,
            'street' => 'Jl. Baru No. 99',
        ]);
    }

    public function test_update_address_validation_error()
    {
        $contact = Contact::factory()->create([
            'user_id' => $this->user->id
        ]);

        $address = Address::factory()->create([
            'contact_id' => $contact->id,
        ]);

        $payload = [
            'street' => '',
            'postal_code' => 'x', // min 3
        ];

        $response = $this->withHeaders($this->headers)
            ->putJson("/api/contacts/{$contact->id}/addresses/{$address->id}", $payload);

        $response->assertStatus(400)
            ->assertJsonValidationErrors(['country', 'postal_code']);
    }

    public function test_update_address_not_found()
    {
        $contact = Contact::factory()->create([
            'user_id' => $this->user->id
        ]);

        $response = $this->withHeaders($this->headers)
            ->putJson("/api/contacts/{$contact->id}/addresses/99999", [
                'street' => 'Jl. Baru No. 99',
                'country' => 'Indonesia',
            ]);

        $response->assertStatus(404)
            ->assertJsonPath('errors.address.0', 'Address not found');
    }

    public function test_unauthenticated_user_cannot_update_address()
    {
        $contact = Contact::factory()->create();
        $address = Address::factory()->create([
            'contact_id' => $contact->id
        ]);

        $response = $this->putJson("/api/contacts/{$contact->id}/addresses/{$address->id}", [
            'street' => 'Jl. Baru No. 99',
            'country' => 'Indonesia',
        ]);

        $response->assertStatus(401)
            ->assertJsonPath('message', 'Unauthenticated.');
    }

    public function test_server_error_when_updating_address()
    {
        $contact = Contact::factory()->create([
            'user_id' => $this->user->id
        ]);

        $address = Address::factory()->create([
            'contact_id' => $contact->id
        ]);

        $mockService = \Mockery::mock(AddressService::class);
        $mockService->shouldReceive('updateAddress')
            ->with($this->user->id, (string) $contact->id, (string) $address->id, \Mockery::any())
            ->andThrow(new Exception('Simulated error'));

        $this->app->instance(AddressService::class, $mockService);

        $response = $this->withHeaders($this->headers)
            ->putJson("/api/contacts/{$contact->id}/addresses/{$address->id}", [
                'street' => 'Jl. Baru No. 99',
                'country' => 'Indonesia',
            ]);

        $response->assertStatus(500)
            ->assertJsonPath('errors.server.0', 'Unexpected error while updating address');
    }

    public function test_user_can_delete_address()
    {
        $contact = Contact::factory()->create([
            'user_id' => $this->user->id
        ]);

        $address = Address::factory()->create([
            'contact_id' => $contact->id
        ]);

        $response = $this->withHeaders($this->headers)
            ->deleteJson("/api/contacts/{$contact->id}/addresses/{$address->id}");

        $response->assertStatus(200)
            ->assertJson(['data' => true]);

        $this->assertDatabaseMissing('addresses', ['id' => $address->id]);
    }

    public function test_unauthenticated_user_cannot_delete_address()
    {
        $contact = Contact::factory()->create();
        $address = Address::factory()->create([
            'contact_id' => $contact->id
        ]);

        $response = $this->deleteJson("/api/contacts/{$contact->id}/addresses/{$address->id}");

        $response->assertStatus(401)
            ->assertJsonPath('message', 'Unauthenticated.');
    }

    public function test_delete_address_not_found()
    {
        $contact = Contact::factory()->create([
            'user_id' => $this->user->id
        ]);

        $response = $this->withHeaders($this->headers)
            ->deleteJson("/api/contacts/{$contact->id}/addresses/99999");

        $response->assertStatus(404)
            ->assertJsonPath('errors.address.0', 'Address not found');
    }

    public function test_server_error_when_deleting_address()
    {
        $contact = Contact::factory()->create([
            'user_id' => $this->user->id
        ]);

        $address = Address::factory()->create([
            'contact_id' => $contact->id
        ]);

        $mockService = \Mockery::mock(AddressService::class);
        $mockService->shouldReceive('deleteAddress')
            ->with($this->user->id, $contact->id, $address->id)
            ->andThrow(new \Exception('Simulated error'));

        $this->app->instance(AddressService::class, $mockService);

        $response = $this->withHeaders($this->headers)
            ->deleteJson("/api/contacts/{$contact->id}/addresses/{$address->id}");

        $response->assertStatus(500)
            ->assertJsonPath('errors.server.0', 'Unexpected error occurred while deleting address');
    }
}
