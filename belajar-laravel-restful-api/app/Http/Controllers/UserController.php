<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\UserLoginRequest;
use App\Http\Requests\UserRegisterRequest;
use App\Http\Requests\UserUpdateRequest;
use App\Http\Resources\UserResource;
use App\Services\AuthService;
use App\Services\UserService;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Throwable;

class UserController extends Controller
{
    public function __construct(
        protected AuthService $authService,
        protected UserService $userService
    ) {}

    public function register(UserRegisterRequest $request): JsonResponse
    {
        try {
            $user = $this->userService->registerUser($request->validated());

            return response()->json([
                'data' => new UserResource($user)
            ], 201);
        } catch (QueryException $e) {
            if ($e->errorInfo[1] === 1062) {
                return response()->json([
                    'errors' => [
                        'username' => ['Username already exists.']
                    ]
                ], 409);
            }

            return response()->json([
                'errors' => [
                    'server' => ['An unexpected error occurred. Please try again later.']
                ]
            ], 500);
        }
    }

    public function login(UserLoginRequest $request): JsonResponse
    {
        try {
            $result = $this->authService->loginUser($request->validated());

            return response()->json([
                'data' => array_merge(
                    (new UserResource($result['user']))->toArray($request),
                    ['token' => $result['token']],
                )
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'errors' => $e->errors(),
            ], 401);
        }
    }


    public function get(Request $request): JsonResponse
    {
        return response()->json([
            'data' => new UserResource($request->user())
        ]);
    }

    public function update(UserUpdateRequest $request): JsonResponse
    {
        $user = Auth::user();

        $updated = $this->userService->updateUser($user, $request->validated());

        return response()->json([
            'data' => new UserResource($updated)
        ]);
    }

    public function logout(): JsonResponse
    {
        try {
            $this->authService->logoutUser(Auth::user());

            return response()->json([
                'data' => true,
            ]);
        } catch (Throwable $e) {
            return response()->json([
                'errors' => [
                    'server' => ['Unexpected error occurred during logout.']
                ]
            ]);
        }
    }
}
