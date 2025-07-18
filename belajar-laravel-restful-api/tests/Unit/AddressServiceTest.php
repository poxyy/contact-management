<?php

namespace Tests\Unit;

use App\Models\Address;
use App\Models\Contact;
use App\Services\AddressService;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AddressServiceTest extends TestCase
{
    use RefreshDatabase;

    protected AddressService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new AddressService();
    }

    public function test_get_addresses_by_contact_returns_collection()
    {
        $contact = Contact::factory()->create();
        Address::factory()->count(3)->create([
            'contact_id' => $contact->id
        ]);

        $result = $this->service->getAddressesByContact($contact->user_id, $contact->id);

        $this->assertInstanceOf(EloquentCollection::class, $result);
        $this->assertCount(3, $result);
    }

    public function test_create_address_returns_address()
    {
        $contact = Contact::factory()->create();

        $data = [
            'street' => 'Jl. Merdeka',
            'city' => 'Jakarta',
            'province' => 'DKI Jakarta',
            'country' => 'Indonesia',
            'postal_code' => '12345'
        ];

        $result = $this->service->createAddress($contact->user_id, $contact->id, $data);

        $this->assertInstanceOf(Address::class, $result);
        $this->assertEquals('Jakarta', $result->city);
    }

    public function test_get_address_by_id_returns_address()
    {
        $contact = Contact::factory()->create();
        $address = Address::factory()->create([
            'contact_id' => $contact->id
        ]);

        $result = $this->service->getAddressById($contact->user_id, $contact->id, $address->id);

        $this->assertInstanceOf(Address::class, $result);
        $this->assertEquals($address->id, $result->id);
    }

    public function test_update_address_returns_updated_data()
    {
        $contact = Contact::factory()->create();
        $address = Address::factory()->create([
            'contact_id' => $contact->id,
            'street' => 'Lama'
        ]);

        $data = ['street' => 'Baru'];

        $result = $this->service->updateAddress($contact->user_id, $contact->id, $address->id, $data);

        $this->assertEquals('Baru', $result['street']);
        $this->assertDatabaseHas('addresses', [
            'id' => $address->id,
            'street' => 'Baru'
        ]);
    }

    public function test_delete_address_removes_it()
    {
        $contact = Contact::factory()->create();
        $address = Address::factory()->create([
            'contact_id' => $contact->id
        ]);

        $result = $this->service->deleteAddress($contact->user_id, $contact->id, $address->id);

        $this->assertTrue($result);
        $this->assertDatabaseMissing('addresses', ['id' => $address->id]);
    }

    public function test_cannot_act_if_contact_does_not_belong_to_user()
    {
        $contact = Contact::factory()->create();
        $anotherUserId = $contact->user_id + 100;

        $result = $this->service->getAddressesByContact($anotherUserId, $contact->id);
        $this->assertNull($result);

        $address = Address::factory()->create(['contact_id' => $contact->id]);

        $update = $this->service->updateAddress($anotherUserId, $contact->id, $address->id, ['street' => 'Fake']);
        $this->assertNull($update);

        $delete = $this->service->deleteAddress($anotherUserId, $contact->id, $address->id);
        $this->assertFalse($delete);
    }
}
