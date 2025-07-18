<?php

use App\Http\Controllers;
use Illuminate\Support\Facades\Route;

Route::post('/users', [Controllers\UserController::class, 'register']);
Route::post('/users/login', [Controllers\UserController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/users/current', [Controllers\UserController::class, 'get']);
    Route::patch('/users/current', [Controllers\UserController::class, 'update']);
    Route::delete('/users/logout', [Controllers\UserController::class, 'logout']);

    Route::get('/contacts', [Controllers\ContactController::class, 'index']);
    Route::post('/contacts', [Controllers\ContactController::class, 'store']);
    Route::get('/contacts/{id}', [Controllers\ContactController::class, 'show'])->where('id', '[0-9]+');
    Route::put('/contacts/{id}', [Controllers\ContactController::class, 'update'])->where('id', '[0-9]+');
    Route::delete('/contacts/{id}', [Controllers\ContactController::class, 'destroy'])->where('id', '[0-9]+');

    Route::get('/contacts/{idContact}/addresses', [Controllers\AddressController::class, 'index'])->where('idContact', '[0-9]+');
    Route::post('/contacts/{idContact}/addresses', [Controllers\AddressController::class, 'store'])->where('idContact', '[0-9]+');
    Route::get('/contacts/{idContact}/addresses/{idAddress}', [Controllers\AddressController::class, 'show'])
        ->where('idContact', '[0-9]+')
        ->where('idAddress', '[0-9]+');
    Route::put('/contacts/{idContact}/addresses/{idAddress}', [Controllers\AddressController::class, 'update'])
        ->where('idContact', '[0-9]+')
        ->where('idAddress', '[0-9]+');
    Route::delete('/contacts/{idContact}/addresses/{idAddress}', [Controllers\AddressController::class, 'destroy'])
        ->where('idContact', '[0-9]+')
        ->where('idAddress', '[0-9]+');
});
