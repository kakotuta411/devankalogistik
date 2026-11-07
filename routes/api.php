<?php

use App\Http\Controllers\Api\AuthController as ApiAuthController;
use App\Http\Controllers\Api\ManifestController as ApiManifestController;
use App\Http\Controllers\Api\ShipmentController as ApiShipmentController;
use App\Http\Controllers\Api\TrackingController;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

// Health check (public, for monitoring)
Route::get('/health', function () {
    try {
        $db = DB::connection()->getPdo() ? 'connected' : 'disconnected';
    } catch (\Exception $e) {
        $db = 'disconnected';
    }
    return response()->json([
        'status' => 'ok',
        'db' => $db,
        'time' => now()->toIso8601String(),
    ]);
})->middleware('throttle:api');

Route::post('/login', [ApiAuthController::class, 'login'])->middleware('throttle:login');

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [ApiAuthController::class, 'logout']);
    Route::apiResource('shipments', ApiShipmentController::class)->names('api.shipments');
    Route::apiResource('manifests', ApiManifestController::class)->names('api.manifests');
    Route::post('/manifests/{id}/dispatch', [ApiManifestController::class, 'dispatch']);
    Route::post('/manifests/{id}/receive', [ApiManifestController::class, 'receive']);
});

// Public tracking (zero PII)
Route::get('/tracking/search', [TrackingController::class, 'search'])->middleware('throttle:tracking');
Route::get('/tracking/{resi}', [TrackingController::class, 'show'])->middleware('throttle:tracking');