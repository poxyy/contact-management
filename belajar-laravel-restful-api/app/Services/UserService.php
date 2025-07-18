<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class UserService
{
    public function registerUser(array $data): User
    {
        if (User::query()->where('username', $data['username'])->exists()) {
            throw ValidationException::withMessages([
                'username' => ['Username already exists.']
            ])->status(409);
        }

        return DB::transaction(function () use ($data) {
            return User::query()->create([
                'username' => $data['username'],
                'password' => $data['password'],
                'name' => $data['name'],
            ]);
        });
    }

    public function updateUser(User $user, array $data): User
    {
        if (isset($data['name'])) {
            $user->name = $data['name'];
        }

        if (isset($data['password'])) {
            $user->password = $data['password'];
        }

        $user->save();

        return $user;
    }
}
