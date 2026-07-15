<?php

// =====================================================================
//  routes/api.php  — النسخة الكاملة مع دمج نظام المفضلة + User Management
// =====================================================================

use App\Http\Controllers\Api\Auth\AuthController;
use App\Http\Controllers\Api\Complaint\GuestComplaintController;
use App\Http\Controllers\Api\Feature\FeatureController;
use App\Http\Controllers\Api\Vehicle\AdminVehicleController;
use App\Http\Controllers\Api\Vehicle\HostVehicleController;
use App\Http\Controllers\Api\Vehicle\VehicleController;
use App\Http\Controllers\Api\Favorite\FavoriteController;   // ← جديد
use App\Http\Controllers\Api\Search\AdminSearchController;
use App\Http\Controllers\Api\Search\GuestSearchController;
use App\Http\Controllers\Api\Search\HostSearchController;
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

    // Host
    Route::prefix('host')->middleware(['auth:sanctum'])->group(function () {

        // ─── Vehicles ────────────────────────────────────

        Route::get('vehicles',              [HostVehicleController::class, 'getHostVehicles']);
        Route::post('vehicles',             [HostVehicleController::class, 'store']);
        Route::get('vehicles/show',         [HostVehicleController::class, 'showForHost']);
        Route::post('vehicles/basic-info', [HostVehicleController::class, 'updateBasicInfo']);

            // ─── Status ──────────────────────────────────────
        Route::post('vehicles/listing-status',[HostVehicleController::class, 'updateListingStatus']);
        Route::post('vehicles/snooze',                   [HostVehicleController::class, 'storeSnooze']);

            // ─── Pricing ─────────────────────────────────────
        Route::post('vehicles/pricing',                           [HostVehicleController::class, 'updatePricing']);
        Route::post('vehicles/custom-pricing',                   [HostVehicleController::class, 'storeCustomPricing']);
        Route::post('vehicles/update/custom-pricing',        [HostVehicleController::class, 'updateCustomPricing']);
        Route::delete('vehicles/delete/custom-pricing',     [HostVehicleController::class, 'destroyCustomPricing']);

            // ─── Images ──────────────────────────────────────
        Route::post('vehicles/uploadImages',                    [HostVehicleController::class, 'uploadImages']);
        Route::delete('vehicles/destroyImage',        [HostVehicleController::class, 'destroyImage']);
        Route::post('vehicles/setPrimaryImage',   [HostVehicleController::class, 'setPrimaryImage']);

            // ─── Features ──────────────────────────────────────
        Route::post('vehicles/features', [HostVehicleController::class, 'syncFeatures']);

            // ─── Availability ──────────────────────────────────────
        Route::post('vehicles/availability', [HostVehicleController::class, 'updateAvailability']);

            // Location
        Route::post('vehicles/location', [HostVehicleController::class, 'updateLocation']);

        // ─── Search & Discovery ──────────────────────────────────────
        Route::get('search', [HostSearchController::class, 'search']);

    });

    // ─── Guest ────────────────────────────────────────────────────────────
    Route::prefix('Guest')->middleware(['auth:sanctum'])->group(function () {

        // ─── Vehicles ───────────────────────────────────────────────
        Route::get   ('vehicles/home',     [VehicleController::class, 'all']);
        Route::get   ('vehicles/cities',   [VehicleController::class, 'cities']);
        Route::get   ('vehicles/delivery', [VehicleController::class, 'delivery']);
        Route::get   ('vehicles/airports', [VehicleController::class, 'airports']);
        Route::get   ('vehicles/nearby',   [VehicleController::class, 'nearby']);
        Route::delete('vehicles/location', [VehicleController::class, 'resetLocation']);

        // ─── Favorites ────────────────────────────────────────────────────────────
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

        // ──  Complaints ────────────────────────────────────────────────
        Route::post('complaints',         [GuestComplaintController::class, 'submitComplaint']);
        Route::get ('complaints/reasons', [GuestComplaintController::class, 'getComplaintReasons']);

        // ─── Search & Discovery ─────────────────────────────────────────────
        Route::prefix('search')->group(function () {
            Route::get('/',       [GuestSearchController::class, 'search']);
            Route::get('filter',  [GuestSearchController::class, 'filter']);
        });

    });

    // ─── Admin ────────────────────────────────────────────────────────────
    Route::prefix('admin')->middleware(['auth:sanctum'])->group(function () {

        // ── Vehicle Management ────────────────────────────────────────────
        Route::get ('vehicles/pending', [AdminVehicleController::class, 'getPendingVehicles']);
        Route::get ('vehicles/show',    [AdminVehicleController::class, 'showPending']);
        Route::post('vehicles/approve', [AdminVehicleController::class, 'approve']);
        Route::post('vehicles/reject',  [AdminVehicleController::class, 'reject']);

        // ──Search & Discovery ───────────────────────────────────────────────
        Route::get('search/users', [AdminSearchController::class, 'searchUsers']);

    });
});
