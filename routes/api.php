<?php

// =====================================================================
//  routes/api.php  — النسخة الكاملة مع دمج نظام المفضلة
// =====================================================================

use App\Http\Controllers\Api\Auth\AuthController;
use App\Http\Controllers\Api\Feature\FeatureController;
use App\Http\Controllers\Api\Vehicle\AdminVehicleController;
use App\Http\Controllers\Api\Vehicle\HostVehicleController;
use App\Http\Controllers\Api\Vehicle\VehicleController;
use App\Http\Controllers\Api\Favorite\FavoriteController;   // ← جديد
use App\Models\VehicleAvailability;
use Illuminate\Support\Facades\Route;


// =====================
//  Auth Routes
// =====================
Route::controller(AuthController::class)->group(function () {   
    Route::post('/register',     [AuthController::class, 'register']);
    Route::post('/login',        [AuthController::class, 'login']);
    Route::post('/googleLogin',  [AuthController::class, 'googleLogin']);
    Route::post('/verify_code',  [AuthController::class, 'verify_code']);
    Route::post('/resend-code',  [AuthController::class, 'resend_code']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
    });
});

// =====================
//  Protected Routes
// =====================
Route::middleware('auth:sanctum')->group(function () {

    // Public (Guest - Host - Admin)
    Route::get('features', [FeatureController::class, 'index']);
    Route::prefix('features')->group(function () {
        Route::post  ('',            [FeatureController::class, 'store']);
        Route::put   ('show/{id}',   [FeatureController::class, 'update']);
        Route::delete('delete/{id}', [FeatureController::class, 'destroy']);
    });

    Route::get('vehicles/show', [VehicleController::class, 'show']);

    // ─── Host ─────────────────────────────────────────────────────────────
    Route::prefix('host')->group(function () {
        Route::get ('vehicles',                   [HostVehicleController::class, 'getHostVehicles']);
        Route::post('vehicles',                   [HostVehicleController::class, 'store']);
        Route::get ('vehicles/show',              [HostVehicleController::class, 'showForHost']);
        Route::put ('vehicles/{id}/details',      [HostVehicleController::class, 'updateDetails']);
        Route::put ('vehicles/{id}/availability', [HostVehicleController::class, 'updateAvailability']);
        Route::put ('vehicles/{id}/pricing',      [HostVehicleController::class, 'updatePricing']);
        Route::put ('vehicles/{id}/features',     [HostVehicleController::class, 'updateFeatures']);
        Route::post('vehicles/{id}/coupons',      [HostVehicleController::class, 'storeCoupon']);
    });

    // ─── Guest ────────────────────────────────────────────────────────────
    Route::prefix('Guest')->group(function () {
        Route::get   ('vehicles/home',     [VehicleController::class, 'all']);
        Route::get   ('vehicles/cities',   [VehicleController::class, 'cities']);
        Route::get   ('vehicles/delivery', [VehicleController::class, 'delivery']);
        Route::get   ('vehicles/airports', [VehicleController::class, 'airports']);
        Route::get   ('vehicles/nearby',   [VehicleController::class, 'nearby']);
        Route::delete('vehicles/location', [VehicleController::class, 'resetLocation']);

        // ─── favorites ────────────────────────────────────────────────────────────
        Route::prefix('favorites')->group(function () {

            Route::get   ('lists',        [FavoriteController::class, 'getAllLists']);
            Route::post  ('lists',        [FavoriteController::class, 'createList']);
            Route::get   ('lists/show',   [FavoriteController::class, 'getList']);
            Route::put   ('lists/rename', [FavoriteController::class, 'renameList']);
            Route::delete('lists/delete', [FavoriteController::class, 'deleteList']);

            Route::post('toggle', [FavoriteController::class, 'toggle']);
            Route::post('move',   [FavoriteController::class, 'move']);
            Route::get ('heart',  [FavoriteController::class, 'heartStatus']);
        });
    });

    // ─── Admin ────────────────────────────────────────────────────────────
    Route::prefix('admin')->group(function () {
        Route::get ('vehicles/pending', [AdminVehicleController::class, 'getPendingVehicles']);
        Route::get ('vehicles/show',    [AdminVehicleController::class, 'showPending']);
        Route::post('vehicles/approve', [AdminVehicleController::class, 'approve']);
        Route::post('vehicles/reject',  [AdminVehicleController::class, 'reject']);
    });
});

Route::get('/testtt', function () {
    $results = VehicleAvailability::query()
        ->with(['vehicle.host.user'])
        ->where('available_to', '<', now())
        ->where('is_blocked', false)
        ->get();

    return response()->json([
        'today'  => now()->toDateString(),
        'count'  => $results->count(),
        'data'   => $results
    ]);
});
