<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\ContactCreateRequest;
use App\Http\Requests\ContactUpdateRequest;
use App\Http\Resources\ContactResource;
use App\Services\ContactService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Throwable;

class ContactController extends Controller
{
    public function __construct(
        protected ContactService $contactService
    ) {}

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $userId = Auth::id();
            $filters = $request->only(['name', 'email', 'phone']);
            $page = max((int) $request->input('page', 1), 1);
            $size = min((int) $request->input('size', 10), 100); // batasi max 100

            $contacts = $this->contactService->search($userId, $filters, $page, $size);

            return response()->json([
                'data' => ContactResource::collection($contacts->items()),
                'meta' => [
                    'page' => $contacts->currentPage(),
                    'size' => $contacts->perPage(),
                    'total' => $contacts->total(),
                ]
            ]);
        } catch (Throwable $e) {
            return response()->json([
                'errors' => [
                    'server' => ['Unexpected error while searching contacts']
                ]
            ], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(ContactCreateRequest $request)
    {
        try {
            $data = $request->validated();
            $userId = Auth::id();

            $contact = $this->contactService->createContact($userId, $data);

            return response()->json([
                'data' => new ContactResource($contact),
            ], 201);
        } catch (Throwable $e) {
            return response()->json([
                'errors' => [
                    'server' => ['Unexpected error while creating contact.']
                ]
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            $userId = Auth::id();

            $contact = $this->contactService->getContactById($userId, $id);

            if (! $contact) {
                return response()->json([
                    'errors' => [
                        'contact' => ['Contact with specified ID not found.']
                    ]
                ], 404);
            }

            return response()->json([
                'data' => new ContactResource($contact)
            ]);
        } catch (Throwable $e) {
            return response()->json([
                'errors' => [
                    'server' => ['Unexpected error while creating contact.']
                ]
            ], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(ContactUpdateRequest $request, string $id)
    {
        try {
            $data = $request->validated();
            $userId = Auth::id();

            $contact = $this->contactService->updateContact($userId, $id, $data);

            if (!$contact) {
                return response()->json([
                    'errors' => [
                        'contact' => ['Contact with ID not found.']
                    ]
                ], 404);
            }

            return response()->json([
                'data' => new ContactResource($contact)
            ]);
        } catch (Throwable $e) {
            return response()->json([
                'errors' => [
                    'server' => ['Unexpected error while updating contact.']
                ]
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $userId = Auth::id();
            $contact = $this->contactService->deleteById($userId, $id);

            if (!$contact) {
                return response()->json([
                    'errors' => [
                        'contact' => ['Contact with specified ID not found.']
                    ]
                ], 404);
            }

            return response()->json([
                'data' => true
            ]);
        } catch (Throwable $e) {
            return response()->json([
                'errors' => [
                    'server' => ['Unexpected error while updating contact.']
                ]
            ], 500);
        }
    }
}
