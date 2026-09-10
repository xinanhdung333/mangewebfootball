<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BookingController;
use App\Http\Controllers\Api\FieldController;
use App\Http\Controllers\Api\ServiceController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')
    ->middleware(['api', 'throttle:api'])
    ->group(function () {
        Route::post('/auth/register', [AuthController::class, 'register'])->middleware('throttle:login');
        Route::post('/auth/login', [AuthController::class, 'login'])->middleware('throttle:login');

        Route::get('/fields', [FieldController::class, 'index']);
        Route::get('/fields/{field}', [FieldController::class, 'show']);
        Route::get('/services', [ServiceController::class, 'index']);
        Route::get('/services/{service}', [ServiceController::class, 'show']);
        Route::post('/bookings/check-available', [BookingController::class, 'checkAvailable']);

        Route::middleware('auth:sanctum')->group(function () {
            Route::get('/auth/me', [AuthController::class, 'me']);
            Route::post('/auth/logout', [AuthController::class, 'logout']);
            Route::apiResource('bookings', BookingController::class);
        });
    });
