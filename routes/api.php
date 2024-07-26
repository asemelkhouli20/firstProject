<?php

use App\Http\Controllers\OfficeController;
use App\Http\Controllers\TagController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get('/tags', TagController::class);

Route::get('/offices', [OfficeController::class, 'index']);
Route::get('/offices/{office}', [OfficeController::class, 'show']);

Route::middleware(['auth:sanctum', 'verified'])->group(function () {
    Route::post('/offices', [OfficeController::class, 'create']);
    Route::put('/offices/{office}', [OfficeController::class, 'update']);
    Route::delete('/offices/{office}', [OfficeController::class, 'delete']);
});
