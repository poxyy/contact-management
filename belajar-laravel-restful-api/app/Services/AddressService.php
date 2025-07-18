<?php

namespace App\Services;

use App\Models\Address;
use App\Models\Contact;
use Illuminate\Support\Collection;

class AddressService
{
    public function getAddressesByContact(int $userId, int $contactId): ?Collection
    {
        $contact = Contact::query()
            ->where('id', $contactId)
            ->where('user_id', $userId)
            ->first();

        if (!$contact) {
            return null;
        }

        return $contact->addresses()->get();
    }

    public function createAddress(int $userId, int $contactId, array $data): ?Address
    {
        $contact = Contact::query()
            ->where('id', $contactId)
            ->where('user_id', $userId)
            ->first();

        if (!$contact) {
            return null;
        }

        return $contact->addresses()->create($data);
    }

    public function getAddressById(int $userId, string $contactId, string $addressId): ?Address
    {
        return Address::where('id', $addressId)
            ->where('contact_id', $contactId)
            ->whereHas('contact', function ($query) use ($userId) {
                $query->where('user_id', $userId);
            })->first();
    }

    public function updateAddress(int $userId, int $contactId, int $addressId, array $data): ?array
    {
        $contact = Contact::where('id', $contactId)
            ->where('user_id', $userId)
            ->first();

        if (! $contact) {
            return null;
        }

        $address = $contact->addresses()->where('id', $addressId)->first();

        if (! $address) {
            return null;
        }

        $address->update($data);

        return $address->only([
            'id',
            'street',
            'city',
            'province',
            'country',
            'postal_code'
        ]);
    }

    public function deleteAddress(int $userId, int $contactId, int $addressId): bool
    {
        $contact = Contact::where('id', $contactId)
            ->where('user_id', $userId)
            ->first();

        if (! $contact) {
            return false;
        }

        $address = $contact->addresses()->where('id', $addressId)->first();

        if (! $address) {
            return false;
        }

        return (bool) $address->delete();
    }
}
