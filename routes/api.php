<?php

use App\Http\Controllers\HostReservationController;
use App\Http\Controllers\OfficeController;
use App\Http\Controllers\OfficeImageController;
use App\Http\Controllers\TagController;
use App\Http\Controllers\UserReservationController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get('/tags', TagController::class);

Route::prefix('offices')->group(function () {
    Route::get('/', [OfficeController::class, 'index']);
    Route::get('/{office}', [OfficeController::class, 'show']);

    Route::middleware(['auth:sanctum', 'verified'])->group(function () {
        Route::post('/', [OfficeController::class, 'create']);
        Route::put('/{office}', [OfficeController::class, 'update']);
        Route::delete('/{office}', [OfficeController::class, 'delete']);
        Route::post('/{office}/images', [OfficeImageController::class, 'store']);
        Route::delete('/{office}/images/{image:id}', [OfficeImageController::class, 'delete']);
    });
});

Route::prefix('reservations')->group(function () {
    Route::middleware(['auth:sanctum', 'verified'])->group(function () {
        Route::get('/', [UserReservationController::class, 'index']);
        Route::post('/', [UserReservationController::class, 'create']);
    });
});
Route::prefix('host/reservations')->group(function () {
    Route::middleware(['auth:sanctum', 'verified'])->group(function () {
        Route::get('/', [HostReservationController::class, 'index']);
    });
});
