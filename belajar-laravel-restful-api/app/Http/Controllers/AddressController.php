<?php

namespace App\Http\Controllers;

use App\Http\Requests\AddressCreateRequest;
use App\Http\Requests\AddressUpdateRequest;
use App\Http\Resources\AddressResource;
use App\Services\AddressService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Throwable;

class AddressController extends Controller
{
    public function __construct(
        protected AddressService $addressService
    ) {}

    /**
     * Display a listing of the resource.
     */
    public function index(string $idContact): JsonResponse
    {
        try {
            $userId = Auth::id();

            $addresses = $this->addressService->getAddressesByContact($userId, (int) $idContact);

            if ($addresses === null) {
                return response()->json([
                    'errors' => [
                        'contact' => ['Contact not found']
                    ]
                ], 404);
            }

            return response()->json([
                'data' => AddressResource::collection($addresses)
            ]);
        } catch (Throwable $e) {
            return response()->json([
                'errors' => [
                    'server' => ['An unexpected error occurred']
                ]
            ], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(AddressCreateRequest $request, string $idContact): JsonResponse
    {
        try {
            $userId = Auth::id();

            $address = $this->addressService->createAddress(
                $userId,
                (int) $idContact,
                $request->validated()
            );

            if (!$address) {
                return response()->json([
                    'errors' => [
                        'contact' => ['Contact with specified ID not found']
                    ]
                ], 404);
            }

            return response()->json([
                'data' => new AddressResource($address)
            ], 201);
        } catch (Throwable $e) {
            return response()->json([
                'errors' => [
                    'server' => ['Unexpected error while creating address']
                ]
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $idContact, string $idAddress): JsonResponse
    {
        try {
            $userId = Auth::id();

            $address = $this->addressService->getAddressById($userId, $idContact, $idAddress);

            if (! $address) {
                return response()->json([
                    'errors' => [
                        'address' => ['Address with specified ID not found']
                    ]
                ], 404);
            }

            return response()->json([
                'data' => new AddressResource($address)
            ]);
        } catch (Throwable $e) {
            return response()->json([
                'errors' => [
                    'server' => ['Unexpected error while retrieving address']
                ]
            ], 500);
        }
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(AddressUpdateRequest $request, string $idContact, string $idAddress): JsonResponse
    {
        try {
            $userId = Auth::id();
            $data = $request->validated();

            $address = $this->addressService->updateAddress($userId, $idContact, $idAddress, $data);

            if (! $address) {
                return response()->json([
                    'errors' => [
                        'address' => ['Address not found']
                    ]
                ], 404);
            }

            return response()->json([
                'data' => $address
            ]);
        } catch (Throwable $e) {
            return response()->json([
                'errors' => [
                    'server' => ['Unexpected error while updating address']
                ]
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $idContact, string $idAddress): JsonResponse
    {
        try {
            $userId = Auth::id();

            $deleted = $this->addressService->deleteAddress($userId, $idContact, $idAddress);

            if (! $deleted) {
                return response()->json([
                    'errors' => [
                        'address' => ['Address not found']
                    ]
                ], 404);
            }

            return response()->json([
                'data' => true
            ]);
        } catch (Throwable $e) {
            return response()->json([
                'errors' => [
                    'server' => ['Unexpected error occurred while deleting address']
                ]
            ], 500);
        }
    }
}
