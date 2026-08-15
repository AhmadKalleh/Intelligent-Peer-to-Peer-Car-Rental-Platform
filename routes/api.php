<?php

use App\Http\Controllers\Api\Account\AccountSwitchController;
use App\Http\Controllers\Api\Statistics\Admin\AdminStatisticsController;
use App\Http\Controllers\Api\Auth\AuthController;
use App\Http\Controllers\Api\Booking\GuestBookingController;
use App\Http\Controllers\Api\Booking\HostBookingController;
use App\Http\Controllers\Api\Handover\HostHandoverController;   // ← جديد
use App\Http\Controllers\Api\Handover\GuestHandoverController;  // ← جديد
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
use App\Http\Controllers\Api\AiChat\AiChatController;
use App\Http\Controllers\Api\Favorite\FavoriteController;   // ← جديد
use App\Http\Controllers\Api\LocationTracking\GuestLocationTrackingController;
use App\Http\Controllers\Api\LocationTracking\HostLocationTrackingController;
use App\Http\Controllers\Api\Notification\NotificationController;
use App\Http\Controllers\Api\Review\GuestReviewController;
use App\Http\Controllers\Api\Review\HostReviewController;
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

Route::get('payments/webhook', [GuestBookingController::class, 'handleWebhook']);
// =====================
//  Protected Routes
// =====================
Route::middleware('auth:sanctum')->group(function () {

    // Public (Guest - Host - Admin)
    Route::get('features', [FeatureController::class, 'index'])->middleware('can:features.index');
    Route::prefix('features')->group(function () {
        Route::post  ('',            [FeatureController::class, 'store'])->middleware('can:features.create');
        Route::put   ('show/{id}',   [FeatureController::class, 'update'])->middleware('can:features.update');
        Route::delete('delete/{id}', [FeatureController::class, 'destroy'])->middleware('can:features.delete');
    });

    Route::prefix('notifications')->group(function () {
        Route::get('unread-count', [NotificationController::class, 'unreadCount'])->middleware('can:notifications.unread-count');
        Route::get('',              [NotificationController::class, 'index'])->middleware('can:notifications.index');
        Route::post('mark-all-read', [NotificationController::class, 'markAllAsRead'])->middleware('can:notifications.mark-all-read');
    });

    Route::get('vehicles/show', [VehicleController::class, 'show'])->middleware('can:vehicles.show');

    // ─── Account Switch (سويتش بين حساب guest و host) ← جديد ───
    Route::post('switch-role', [AccountSwitchController::class, 'switch']);
    Route::post('vehicles/store',             [HostVehicleController::class, 'store']);
    Route::get('guests/show', [GuestUserController::class, 'showGuestDetails']);

// ─── Live Location Tracking (تتبع توصيل السيارة) ← جديد ────
            Route::post('track-location', [GuestLocationTrackingController::class, 'update'])
                ->middleware(['can:tracking.update', 'throttle:40,1']);
            Route::get('track-location', [GuestLocationTrackingController::class, 'show'])
                ->middleware('can:tracking.show');

    // Host
    Route::prefix('host')->group(function () {

        // ── Host Profile ──────────────────────────────────────────────────
        Route::post('profile/image', [HostUserController::class, 'updateProfileImage'])->middleware('can:profile.image');
        Route::get ('hosts/show',            [HostUserController::class, 'showHostDetails'])->middleware('can:profile.show');
         Route::get ('hosts/id',              [HostUserController::class, 'getHostId'])->middleware('can:profile.show'); // ← جديد
        Route::post('hosts/change-password', [HostUserController::class, 'changeHostPassword'])->middleware('can:profile.change-password');

        // ─── Vehicles ────────────────────────────────────

        Route::get('vehicles',              [HostVehicleController::class, 'getHostVehicles'])->middleware('can:vehicles.index-own');
        Route::post('vehicles',             [HostVehicleController::class, 'store'])->middleware('can:vehicles.create');
        Route::get('vehicles/show',         [HostVehicleController::class, 'showForHost'])->middleware('can:vehicles.show-own');
        Route::post('vehicles/basic-info', [HostVehicleController::class, 'updateBasicInfo'])->middleware('can:vehicles.update');

            Route::prefix('chat')->group(function () {
            Route::post('open',     [ConversationController::class, 'open'])->middleware('can:conversations.open');
            Route::get ('list',     [ConversationController::class, 'list'])->middleware('can:conversations.index');
            Route::get ('show',     [ConversationController::class, 'show'])->middleware('can:conversations.show');
            Route::get ('messages', [ConversationController::class, 'messages'])->middleware('can:messages.index');
            Route::post('send',     [ConversationController::class, 'send'])->middleware('can:messages.send');
            Route::post('read',     [ConversationController::class, 'markAsRead'])->middleware('can:messages.mark-read');
            Route::get ('unread',   [ConversationController::class, 'unreadCount'])->middleware('can:messages.unread-count');
        });
         // ──  Complaints ────────────────────────────────────────────────
        Route::post('complaints',         [GuestComplaintController::class, 'submitComplaint'])->middleware('can:complaints.create-own');
        Route::get ('complaints/reasons', [GuestComplaintController::class, 'getComplaintReasons'])->middleware('can:complaints.reasons');
            // ─── Status ──────────────────────────────────────
        Route::post('vehicles/listing-status',[HostVehicleController::class, 'updateListingStatus'])->middleware('can:vehicles.update-listing-status');
        Route::post('vehicles/snooze',                   [HostVehicleController::class, 'storeSnooze'])->middleware('can:vehicle-availability.snooze');

            // ─── Pricing ─────────────────────────────────────
        Route::post('vehicles/pricing',                           [HostVehicleController::class, 'updatePricing'])->middleware('can:vehicle-pricing.update');
        Route::post('vehicles/custom-pricing',                   [HostVehicleController::class, 'storeCustomPricing'])->middleware('can:vehicle-pricing.create');
        Route::post('vehicles/update/custom-pricing',        [HostVehicleController::class, 'updateCustomPricing'])->middleware('can:vehicle-pricing.update');
        Route::delete('vehicles/delete/custom-pricing',     [HostVehicleController::class, 'destroyCustomPricing'])->middleware('can:vehicle-pricing.delete');

            // ─── Images ──────────────────────────────────────
        Route::post('vehicles/uploadImages',                    [HostVehicleController::class, 'uploadImages'])->middleware('can:vehicles.images.upload');
        Route::delete('vehicles/destroyImage',        [HostVehicleController::class, 'destroyImage'])->middleware('can:vehicles.images.delete');
        Route::post('vehicles/setPrimaryImage',   [HostVehicleController::class, 'setPrimaryImage'])->middleware('can:vehicles.images.set-primary');

            // ─── Features ──────────────────────────────────────
        Route::post('vehicles/features', [HostVehicleController::class, 'syncFeatures'])->middleware('can:vehicles.features.sync');

            // ─── Availability ──────────────────────────────────────
        Route::post('vehicles/availability', [HostVehicleController::class, 'updateAvailability'])->middleware('can:vehicle-availability.update');

            // Location
        Route::post('vehicles/location', [HostVehicleController::class, 'updateLocation'])->middleware('can:vehicles.update-location');

        // ─── Search & Discovery ──────────────────────────────────────
        Route::get('search', [HostSearchController::class, 'search'])->middleware('can:search.host');

        // ─── Coupons ───────────────────────────────────────────────
        Route::prefix('coupons')->group(function () {
            Route::get   ('',              [HostCouponController::class, 'index'])->middleware('can:coupons.index-own');
            Route::post  ('',              [HostCouponController::class, 'store'])->middleware('can:coupons.create');
            Route::post   ('/update',          [HostCouponController::class, 'update'])->middleware('can:coupons.update');
            Route::post   ('/toggle',   [HostCouponController::class, 'toggleStatus'])->middleware('can:coupons.toggle');
            Route::delete('/delete',          [HostCouponController::class, 'destroy'])->middleware('can:coupons.delete');
            Route::get('/uses',      [HostCouponController::class, 'showUses'])->middleware('can:coupons.uses');
        });

        // ─── Host Bookings ────────────────────────────────────────
        Route::get('bookings',      [HostBookingController::class, 'index'])->middleware('can:bookings.index-own');
        Route::get('bookings/current', [HostBookingController::class, 'getActiveBookings'])->middleware('can:bookings.active-own'); // ← جديد
        Route::get('bookings/confirmed', [HostBookingController::class, 'getConfirmedBookings'])->middleware('can:bookings.confirmed-own'); // ← جديد
        // ─── Vehicle Handover (استلام/تسليم السيارة) ← جديد ───────
        Route::prefix('bookings/handover')->group(function () {
            Route::post('generate', [HostHandoverController::class, 'generate'])->middleware('can:handover.generate');
        });
        // ─── Reviews (تقييم الهوست) ← جديد ─────────────────────────
        Route::get('reviews/rating', [HostReviewController::class, 'averageRating'])->middleware('can:reviews.rating-own');

        
        // ─── Live Location Tracking (تتبع توصيل السيارة) ← جديد ────
        Route::prefix('bookings')->group(function () {
            Route::post('track-location', [HostLocationTrackingController::class, 'update']);
            Route::get('track-location', [HostLocationTrackingController::class, 'show']);
        });
    });

    // ─── Guest ────────────────────────────────────────────────────────────
    Route::prefix('Guest')->group(function () {

        // ─── Vehicles ───────────────────────────────────────────────
        Route::get   ('vehicles/home',     [VehicleController::class, 'all'])->middleware('can:vehicles.browse');
        Route::get   ('vehicles/cities',   [VehicleController::class, 'cities'])->middleware('can:vehicles.browse');
        Route::get   ('vehicles/delivery', [VehicleController::class, 'delivery'])->middleware('can:vehicles.browse');
        Route::get   ('vehicles/airports', [VehicleController::class, 'airports'])->middleware('can:vehicles.browse');
        Route::get   ('vehicles/nearby',   [VehicleController::class, 'nearby'])->middleware('can:vehicles.browse');
        Route::delete('vehicles/location', [VehicleController::class, 'resetLocation'])->middleware('can:vehicles.location.reset');
        Route::post('vehicles',             [HostVehicleController::class, 'store'])->middleware('can:vehicles.create');



        // ── Guest Profile ─────────────────────────────────────────────────
        Route::post('profile/image', [GuestUserController::class, 'updateProfileImage'])->middleware('can:profile.image');
        Route::post('guests/change-password', [GuestUserController::class, 'changeGuestPassword'])->middleware('can:profile.change-password');
        Route::get('guests/show', [GuestUserController::class, 'showGuestDetails'])->middleware('can:profile.show');



        // ─── Favorites ────────────────────────────────────────────────────

        Route::prefix('favorites')->group(function () {
            Route::get   ('lists',        [FavoriteController::class, 'getAllLists'])->middleware('can:favorites.index');
            Route::post  ('lists',        [FavoriteController::class, 'createList'])->middleware('can:favorites.create-list');
            Route::get   ('lists/show',   [FavoriteController::class, 'getList'])->middleware('can:favorites.show-list');
            Route::put   ('lists/rename', [FavoriteController::class, 'renameList'])->middleware('can:favorites.rename-list');
            Route::delete('lists/delete', [FavoriteController::class, 'deleteList'])->middleware('can:favorites.delete-list');

            Route::post('toggle', [FavoriteController::class, 'toggle'])->middleware('can:favorites.toggle');
            Route::post('move',   [FavoriteController::class, 'move'])->middleware('can:favorites.move');
            Route::get ('heart',  [FavoriteController::class, 'heartStatus'])->middleware('can:favorites.heart-status');
        });

        // ──  Complaints ────────────────────────────────────────────────
        Route::post('complaints',         [GuestComplaintController::class, 'submitComplaint'])->middleware('can:complaints.create-own');
        Route::get ('complaints/reasons', [GuestComplaintController::class, 'getComplaintReasons'])->middleware('can:complaints.reasons');



        // ── Guest Chat ────────────────────────────────────────────────────
        Route::prefix('chat')->group(function () {
            Route::post('open',     [ConversationController::class, 'open'])->middleware('can:conversations.open');
            Route::get ('list',     [ConversationController::class, 'list'])->middleware('can:conversations.index');
            Route::get ('show',     [ConversationController::class, 'show'])->middleware('can:conversations.show');
            Route::get ('messages', [ConversationController::class, 'messages'])->middleware('can:messages.index');
            Route::post('send',     [ConversationController::class, 'send'])->middleware('can:messages.send');
            Route::post('read',     [ConversationController::class, 'markAsRead'])->middleware('can:messages.mark-read');
            Route::get ('unread',   [ConversationController::class, 'unreadCount'])->middleware('can:messages.unread-count');
        });

        // ─── Search & Discovery ─────────────────────────────────────────────
        Route::prefix('search')->group(function () {
            Route::get('/',       [GuestSearchController::class, 'search'])->middleware('can:vehicles.search');
            Route::get('filter',  [GuestSearchController::class, 'filter'])->middleware('can:vehicles.filter');
        });

        // ─── Coupons ───────────────────────────────────────────────
        Route::prefix('coupons')->group(function () {
            Route::post('/validate', [GuestCouponController::class, 'validateCoupon'])->middleware('can:coupons.apply');
        });

        // ─── Guest Bookings ───────────────────────────────────────

        Route::prefix('bookings')->group(function () {
            Route::post('calculate',              [GuestBookingController::class, 'calculatePrice'])->middleware('can:bookings.calculate');
            Route::post('create',                        [GuestBookingController::class, 'createBooking'])->middleware('can:bookings.create');
            Route::get('list',                         [GuestBookingController::class, 'index'])->middleware('can:bookings.index-own');
            Route::delete('cancel',                 [GuestBookingController::class, 'cancelBooking'])->middleware('can:bookings.cancel');
            Route::get('payment-status',     [GuestBookingController::class, 'checkPayment'])->middleware('can:payments.status-own');
            Route::get('current',            [GuestBookingController::class, 'getActiveBooking'])->middleware('can:bookings.active-own'); // ← جديد
            Route::get('confirmed',          [GuestBookingController::class, 'getConfirmedBooking'])->middleware('can:bookings.confirmed-own'); // ← جديد
            // ─── Vehicle Handover (استلام/تسليم السيارة) ← جديد ───
            Route::prefix('handover')->group(function () {
                Route::post('confirm', [GuestHandoverController::class, 'confirm'])->middleware('can:handover.confirm');
            });
        });
                // ─── AI Chat (مساعد ذكي خاص بالضيف فقط) ────────────────────────────
        Route::prefix('ai-chat')->group(function () {
            Route::post('send',         [AiChatController::class, 'send'])->middleware('can:ai-chat.send');
            Route::get('messages',      [AiChatController::class, 'index'])->middleware('can:ai-chat.messages');
            Route::get('unread-count',  [AiChatController::class, 'unreadCount'])->middleware('can:ai-chat.unread-count');
            Route::post('read',         [AiChatController::class, 'markAsRead'])->middleware('can:ai-chat.mark-read');
        });

        // ─── Reviews (تقييم الهوست بعد انتهاء الرحلة) ← جديد ────────
        Route::post('reviews', [GuestReviewController::class, 'submit'])->middleware('can:reviews.create');
         Route::prefix('bookings')->group(function () {
            Route::post('track-location', [HostLocationTrackingController::class, 'update']); // بحد أقصى ~ تحديث كل 1.5 ثانية
            Route::get('track-location', [HostLocationTrackingController::class, 'show']);
        });

    });

    // ─── Admin ────────────────────────────────────────────────────────────
    Route::prefix('admin')->group(function () {

        // ── Vehicle Management ────────────────────────────────────────────
        Route::get ('vehicles/pending', [AdminVehicleController::class, 'getPendingVehicles'])->middleware('can:admin.vehicles.index-pending');
        Route::get ('vehicles/show',    [AdminVehicleController::class, 'showPending'])->middleware('can:admin.vehicles.show-pending');
        Route::post('vehicles/approve', [AdminVehicleController::class, 'approve'])->middleware('can:admin.vehicles.approve');
        Route::post('vehicles/reject',  [AdminVehicleController::class, 'reject'])->middleware('can:admin.vehicles.reject');

        // ── User Management ───────────────────────────────────────────────
        Route::get   ('users',                 [AdminUserController::class, 'getUsers'])->middleware('can:admin.users.index');
        Route::post  ('users',                 [AdminUserController::class, 'addUser'])->middleware('can:admin.users.create');
        Route::post  ('users/promote-to-host', [AdminUserController::class, 'promoteGuestToHost'])->middleware('can:admin.users.promote-to-host');
        Route::post  ('users/toggle-status',   [AdminUserController::class, 'toggleGuestStatus'])->middleware('can:admin.users.toggle-status');
        Route::delete('users/guest',           [AdminUserController::class, 'deleteGuest'])->middleware('can:admin.users.delete-guest');
        Route::delete('users/host',            [AdminUserController::class, 'deleteHost'])->middleware('can:admin.users.delete-host');
        Route::post  ('users/profile/image',   [AdminUserController::class, 'updateProfileImage'])->middleware('can:admin.users.update-profile-image');

        // ── Complaints Management ───────────────────────────────────────────
        Route::get ('complaints',                   [AdminComplaintController::class, 'getComplaints'])->middleware('can:admin.complaints.index');
        Route::post('complaints/reply',              [AdminComplaintController::class, 'replyToComplaint'])->middleware('can:admin.complaints.reply');
        Route::get ('complaints/unanswered-count',   [AdminComplaintController::class, 'countUnanswered'])->middleware('can:admin.complaints.unanswered-count');


        // ──Search & Discovery ───────────────────────────────────────────────
        Route::get('search/users', [AdminSearchController::class, 'searchUsers'])->middleware('can:admin.search.users');

        Route::get('statistics', [AdminStatisticsController::class, 'index'])->middleware('can:admin.stats.view');
    });
});


