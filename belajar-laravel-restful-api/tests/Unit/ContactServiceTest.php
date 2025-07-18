<?php

namespace Tests\Unit;

use App\Models\Contact;
use App\Models\User;
use App\Services\ContactService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContactServiceTest extends TestCase
{
    use RefreshDatabase;

    protected ContactService $service;
    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = new ContactService();
        $this->user = User::factory()->create();
    }

    public function test_it_can_create_a_contact()
    {
        $data = [
            'first_name' => 'Jane',
            'last_name' => 'Doe',
            'email' => 'jane.doe@mail.com',
            'phone' => '083232872922',
        ];

        $contact = $this->service->createContact($this->user->id, $data);

        $this->assertInstanceOf(Contact::class, $contact);
        $this->assertDatabaseHas('contacts', [
            'user_id' => $this->user->id,
            'first_name' => 'Jane'
        ]);
    }

    public function test_it_can_get_contact_by_id()
    {
        $contact = Contact::factory()->create(['user_id' => $this->user->id]);

        $found = $this->service->getContactById($this->user->id, $contact->id);

        $this->assertNotNull($found);
        $this->assertEquals($contact->id, $found->id);
    }

    public function test_it_returns_null_if_contact_not_owned_by_user()
    {
        $contact = Contact::factory()->create();

        $found = $this->service->getContactById($this->user->id, $contact->id);

        $this->assertNull($found);
    }

    public function test_it_can_update_contact()
    {
        $contact = Contact::factory()->create(['user_id' => $this->user->id]);

        $data = [
            'first_name' => 'Updated',
            'email' => 'updated@example.com',
            'phone' => '089999999',
        ];

        $updated = $this->service->updateContact($this->user->id, $contact->id, $data);

        $this->assertNotNull($updated);
        $this->assertEquals('Updated', $updated->first_name);
        $this->assertDatabaseHas('contacts', [
            'id' => $contact->id,
            'email' => 'updated@example.com'
        ]);
    }

    public function test_it_cannot_update_contact_of_other_user()
    {
        $contact = Contact::factory()->create();

        $result = $this->service->updateContact($this->user->id, $contact->id, [
            'first_name' => 'Hack',
        ]);

        $this->assertNull($result);
    }

    public function test_it_can_delete_contact()
    {
        $contact = Contact::factory()->create(['user_id' => $this->user->id]);

        $result = $this->service->deleteById($this->user->id, $contact->id);

        $this->assertTrue($result);
        $this->assertDatabaseMissing('contacts', [
            'id' => $contact->id,
        ]);
    }

    public function test_it_cannot_delete_other_users_contact()
    {
        $contact = Contact::factory()->create();

        $result = $this->service->deleteById($this->user->id, $contact->id);

        $this->assertFalse($result);
    }

    public function test_it_can_search_contacts_by_name()
    {
        Contact::factory()->create([
            'user_id' => $this->user->id,
            'first_name' => 'Alice',
        ]);
        Contact::factory()->create([
            'user_id' => $this->user->id,
            'first_name' => 'Bob',
        ]);

        $results = $this->service->search($this->user->id, ['name' => 'Ali']);

        $this->assertCount(1, $results->items());
        $this->assertEquals('Alice', $results->items()[0]->first_name);
        $this->assertEquals(1, $results->total());
    }
}