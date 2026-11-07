<?php

use App\Http\Controllers\Api\AuthController as ApiAuthController;
use App\Http\Controllers\Api\ManifestController as ApiManifestController;
use App\Http\Controllers\Api\ShipmentController as ApiShipmentController;
use App\Http\Controllers\Api\TrackingController;
use Illuminate\Support\Facades\Route;

Route::post('/login', [ApiAuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [ApiAuthController::class, 'logout']);
    Route::apiResource('shipments', ApiShipmentController::class)->names('api.shipments');
    Route::apiResource('manifests', ApiManifestController::class)->names('api.manifests');
    Route::post('/manifests/{id}/dispatch', [ApiManifestController::class, 'dispatch']);
    Route::post('/manifests/{id}/receive', [ApiManifestController::class, 'receive']);
});

// Public tracking (zero PII)
Route::get('/tracking/search', [TrackingController::class, 'search']);
Route::get('/tracking/{resi}', [TrackingController::class, 'show']);
