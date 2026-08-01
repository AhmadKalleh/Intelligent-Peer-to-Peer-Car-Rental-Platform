<?php


use App\Http\Controllers\Api\Auth\AuthController;
use App\Http\Controllers\Api\Coupon\GuestCouponController;
use App\Http\Controllers\Api\Coupon\HostCouponController;
use App\Http\Controllers\Api\Feature\FeatureController;
use App\Http\Controllers\Api\Vehicle\AdminVehicleController;
use App\Http\Controllers\Api\Vehicle\HostVehicleController;
use App\Http\Controllers\Api\Vehicle\VehicleController;
use App\Http\Controllers\Api\User\AdminUserController;
use App\Http\Controllers\Api\User\HostUserController;
use App\Http\Controllers\Api\User\GuestUserController;
use App\Http\Controllers\Api\Complaint\AdminComplaintController;
use App\Http\Controllers\Api\Complaint\HostComplaintController;
use App\Http\Controllers\Api\Complaint\GuestComplaintController;
use App\Http\Controllers\Api\Conversation\ConversationController;
use App\Models\VehicleAvailability;
use App\Http\Controllers\Api\Favorite\FavoriteController;   // ← جديد
use App\Http\Controllers\Api\Notification\NotificationController;
use App\Http\Controllers\Api\Search\AdminSearchController;
use App\Http\Controllers\Api\Search\GuestSearchController;
use App\Http\Controllers\Api\Search\HostSearchController;
use App\Services\Notification\NotificationService;
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

    Route::prefix('notifications')->group(function () {
        Route::get('unread-count', [NotificationController::class, 'unreadCount']);
        Route::get('',              [NotificationController::class, 'index']);
        Route::post('mark-all-read', [NotificationController::class, 'markAllAsRead']);
    });

    Route::get('vehicles/show', [VehicleController::class, 'show']);


    // Host
    Route::prefix('host')->group(function () {

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

        // ─── Coupons ───────────────────────────────────────────────
        Route::prefix('coupons')->group(function () {
            Route::get   ('',              [HostCouponController::class, 'index']);
            Route::post  ('',              [HostCouponController::class, 'store']);
            Route::post   ('/update',          [HostCouponController::class, 'update']);
            Route::post   ('/toggle',   [HostCouponController::class, 'toggleStatus']);
            Route::delete('/delete',          [HostCouponController::class, 'destroy']);
            Route::get('/uses',      [HostCouponController::class, 'showUses']);
        });

    });

    // ─── Guest ────────────────────────────────────────────────────────────
    Route::prefix('Guest')->group(function () {

        // ─── Vehicles ───────────────────────────────────────────────
        Route::get   ('vehicles/home',     [VehicleController::class, 'all']);
        Route::get   ('vehicles/cities',   [VehicleController::class, 'cities']);
        Route::get   ('vehicles/delivery', [VehicleController::class, 'delivery']);
        Route::get   ('vehicles/airports', [VehicleController::class, 'airports']);
        Route::get   ('vehicles/nearby',   [VehicleController::class, 'nearby']);
        Route::delete('vehicles/location', [VehicleController::class, 'resetLocation']);



        // ── Guest Profile ─────────────────────────────────────────────────
        Route::post('profile/image', [GuestUserController::class, 'updateProfileImage']);
        Route::post('guests/change-password', [GuestUserController::class, 'changeGuestPassword']);
        Route::get('guests/show', [GuestUserController::class, 'showGuestDetails']);



        // ─── Favorites ────────────────────────────────────────────────────

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



        // ── Guest Chat ────────────────────────────────────────────────────
        Route::prefix('chat')->group(function () {
            Route::post('open',     [ConversationController::class, 'open']);
            Route::get ('list',     [ConversationController::class, 'list']);
            Route::get ('show',     [ConversationController::class, 'show']);
            Route::get ('messages', [ConversationController::class, 'messages']);
            Route::post('send',     [ConversationController::class, 'send']);
            Route::post('read',     [ConversationController::class, 'markAsRead']);
            Route::get ('unread',   [ConversationController::class, 'unreadCount']);
        });

        // ─── Search & Discovery ─────────────────────────────────────────────
        Route::prefix('search')->group(function () {
            Route::get('/',       [GuestSearchController::class, 'search']);
            Route::get('filter',  [GuestSearchController::class, 'filter']);
        });

        // ─── Coupons ───────────────────────────────────────────────
        Route::prefix('coupons')->group(function () {
            Route::post('/validate', [GuestCouponController::class, 'validateCoupon']);
        });

    });

    // ─── Admin ────────────────────────────────────────────────────────────
    Route::prefix('admin')->group(function () {

        // ── Vehicle Management ────────────────────────────────────────────
        Route::get ('vehicles/pending', [AdminVehicleController::class, 'getPendingVehicles']);
        Route::get ('vehicles/show',    [AdminVehicleController::class, 'showPending']);
        Route::post('vehicles/approve', [AdminVehicleController::class, 'approve']);
        Route::post('vehicles/reject',  [AdminVehicleController::class, 'reject']);

        // ── User Management ───────────────────────────────────────────────
        Route::get   ('users',                 [AdminUserController::class, 'getUsers']);
        Route::post  ('users',                 [AdminUserController::class, 'addUser']);
        Route::post  ('users/promote-to-host', [AdminUserController::class, 'promoteGuestToHost']);
        Route::post  ('users/toggle-status',   [AdminUserController::class, 'toggleGuestStatus']);
        Route::delete('users/guest',           [AdminUserController::class, 'deleteGuest']);
        Route::delete('users/host',            [AdminUserController::class, 'deleteHost']);
        Route::post  ('users/profile/image',   [AdminUserController::class, 'updateProfileImage']);

        // ── Complaints Management ───────────────────────────────────────────
        Route::get ('complaints',                   [AdminComplaintController::class, 'getComplaints']);
        Route::post('complaints/reply',              [AdminComplaintController::class, 'replyToComplaint']);
        Route::get ('complaints/unanswered-count',   [AdminComplaintController::class, 'countUnanswered']);


        // ──Search & Discovery ───────────────────────────────────────────────
        Route::get('search/users', [AdminSearchController::class, 'searchUsers']);
    });
});


