<?php

namespace App\Services;

use App\Models\Contact;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ContactService
{
    public function search(int $userId, array $filters, int $page = 1, int $size = 10): LengthAwarePaginator
    {
        return Contact::query()
            ->where('user_id', $userId)
            ->when(
                $filters['name'] ?? null,
                fn($q, $v) =>
                $q->where(function ($query) use ($v) {
                    $query->where('first_name', 'like', "%{$v}%")
                        ->orWhere('last_name', 'like', "%{$v}%");
                })
            )
            ->when($filters['email'] ?? null, fn($q, $v) => $q->where('email', 'like', "%{$v}%"))
            ->when($filters['phone'] ?? null, fn($q, $v) => $q->where('phone', 'like', "%{$v}%"))
            ->paginate($size, ['*'], 'page', $page);
    }

    public function createContact(int $userId, array $data): Contact
    {
        return Contact::query()->create([
            'user_id' => $userId,
            ...$data,
        ]);
    }

    public function getContactById(int $userId, string $id): ?Contact
    {
        return Contact::query()
            ->where('id', $id)
            ->where('user_id', $userId)
            ->first();
    }

    public function updateContact(int $userId, string $id, array $data): ?Contact
    {
        $contact = $this->getContactById($userId, $id);

        if ($contact) {
            $contact->update($data);
        }

        return $contact;
    }

    public function deleteById(int $userId, string $id): bool
    {
        $contact = $this->getContactById($userId, $id);

        return $contact ? $contact->delete() : false;
    }
}
