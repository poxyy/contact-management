<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthService
{
    /**
     * @var User @user
     */
    public function loginUser(array $credentials): array
    {
        $user = User::query()->where('username', $credentials['username'])->first();

        if (! $user || !Hash::check($credentials['password'], $user->password)) {
            throw ValidationException::withMessages([
                'credentials' => ['Invalid username or password.']
            ]);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return [
            'user' => $user,
            'token' => $token,
        ];
    }

    public function logoutUser(User $user): void
    {
        /**
         * @var \Laravel\Sanctum\PersonalAccessToken|null $token
         */
        $token = $user->currentAccessToken();
        $token?->delete();
    }
}
