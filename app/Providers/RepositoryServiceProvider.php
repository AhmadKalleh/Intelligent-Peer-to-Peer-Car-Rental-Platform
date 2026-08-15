<?php

namespace App\Providers;

use App\Repositories\AiChat\AiChatRepository;
use App\Repositories\AiChat\Interfaces\AiChatRepositoryInterface;
use App\Repositories\Auth\AuthRepository;
use App\Repositories\Auth\Interfaces\AuthRepositoryInterface;

use App\Repositories\Complaint\Admin\ComplaintAdminRepository;
use App\Repositories\Complaint\ComplaintRepository;
use App\Repositories\Complaint\Interfaces\ComplaintAdminRepositoryInterface;
use App\Repositories\Complaint\Interfaces\ComplaintRepositoryInterface;

use App\Repositories\Conversation\ConversationRepository;
use App\Repositories\Conversation\Interfaces\ConversationRepositoryInterface;

use App\Repositories\Favorite\FavoriteRepository;
use App\Repositories\Favorite\Interfaces\FavoriteRepositoryInterface;

use App\Repositories\Feature\FeatureRepository;
use App\Repositories\Feature\Interfaces\FeatureRepositoryInterface;

use App\Repositories\Search\Interfaces\SearchAdminRepositoryInterface;
use App\Repositories\Search\Interfaces\SearchQueryRepositoryInterface;
use App\Repositories\Search\SearchAdminRepository;
use App\Repositories\Search\SearchQueryRepository;

use App\Repositories\User\Admin\UserAdminRepository;
use App\Repositories\User\Guest\UserGuestRepository;
use App\Repositories\User\Guest\UserImageRepository;
use App\Repositories\User\Host\UserHostRepository;
use App\Repositories\User\Interfaces\UserAdminRepositoryInterface;
use App\Repositories\User\Interfaces\UserGuestRepositoryInterface;
use App\Repositories\User\Interfaces\UserHostRepositoryInterface;
use App\Repositories\User\Interfaces\UserImageRepositoryInterface;

use App\Repositories\Vehicle\Interfaces\VehicleAdminRepositoryInterface;
use App\Repositories\Vehicle\Interfaces\VehicleCommandRepositoryInterface;
use App\Repositories\Vehicle\Interfaces\VehicleQueryRepositoryInterface;
use App\Repositories\Vehicle\VehicelAdminRepository;
use App\Repositories\Vehicle\VehicelCommandRepository;
use App\Repositories\Vehicle\VehicelQueryRepository;

use App\Repositories\Coupon\CouponGuestRepository;
use App\Repositories\Coupon\CouponHostRepository;
use App\Repositories\Coupon\Interfaces\CouponGuestRepositoryInterface;
use App\Repositories\Coupon\Interfaces\CouponHostRepositoryInterface;

use App\Repositories\Notification\Interfaces\NotificationRepositoryInterface;
use App\Repositories\Notification\NotificationRepository;

use App\Repositories\Booking\BookingGuestRepository;
use App\Repositories\Booking\BookingHostRepository;
use App\Repositories\Booking\Interfaces\BookingGuestRepositoryInterface;
use App\Repositories\Booking\Interfaces\BookingHostRepositoryInterface;

// ← جديد: Handover (استلام/تسليم السيارة)
use App\Repositories\Handover\HandoverGuestRepository;
use App\Repositories\Handover\HandoverHostRepository;
use App\Repositories\Handover\Interfaces\HandoverGuestRepositoryInterface;
use App\Repositories\Handover\Interfaces\HandoverHostRepositoryInterface;

use App\Repositories\Review\ReviewGuestRepository;
use App\Repositories\Review\ReviewHostRepository;
use App\Repositories\Review\Interfaces\ReviewGuestRepositoryInterface;
use App\Repositories\Review\Interfaces\ReviewHostRepositoryInterface;
use App\Repositories\Statistics\Admin\AdminStatisticsRepository;
use App\Repositories\Statistics\Admin\Interfaces\AdminStatisticsRepositoryInterface;
use Illuminate\Support\ServiceProvider;


// ← جديد: LocationTracking (تتبع موقع التوصيل اللحظي)
use App\Repositories\LocationTracking\LocationTrackingHostRepository;
use App\Repositories\LocationTracking\LocationTrackingGuestRepository;
use App\Repositories\LocationTracking\Interfaces\LocationTrackingHostRepositoryInterface;
use App\Repositories\LocationTracking\Interfaces\LocationTrackingGuestRepositoryInterface;

use App\Repositories\Account\AccountSwitchRepository;
use App\Repositories\Account\Interfaces\AccountSwitchRepositoryInterface;

class RepositoryServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(
            AuthRepositoryInterface::class,
            AuthRepository::class
        );

        // ── User Management ───────────────────────────────────────────────────
        $this->app->bind(UserAdminRepositoryInterface::class, UserAdminRepository::class);
        $this->app->bind(UserHostRepositoryInterface::class, UserHostRepository::class);
        $this->app->bind(UserGuestRepositoryInterface::class, UserGuestRepository::class);
        $this->app->bind(UserImageRepositoryInterface::class, UserImageRepository::class);

        // ── Complaints ────────────────────────────────────────────────────────
        $this->app->bind(ComplaintRepositoryInterface::class, ComplaintRepository::class);
        $this->app->bind(ComplaintAdminRepositoryInterface::class, ComplaintAdminRepository::class);

        // ── Conversation ──────────────────────────────────────────────────────
        $this->app->bind(
            ConversationRepositoryInterface::class,
            ConversationRepository::class
        );

        // ── Search ────────────────────────────────────────────────────────────
        $this->app->bind(
            SearchAdminRepositoryInterface::class,
            SearchAdminRepository::class
        );

        $this->app->bind(
            SearchQueryRepositoryInterface::class,
            SearchQueryRepository::class
        );

        // ── AI Chat (Guest ↔ AI Assistant) ───────────────────────────────────
        $this->app->bind(
            AiChatRepositoryInterface::class,
            AiChatRepository::class
        );

        // ── Notifications ────────────────────────────────────────────────────
        $this->app->bind(
            NotificationRepositoryInterface::class,
            NotificationRepository::class
        );

        // ── Bookings ─────────────────────────────────────────────────────────
        $this->app->bind(
            BookingGuestRepositoryInterface::class,
            BookingGuestRepository::class
        );

        $this->app->bind(
            BookingHostRepositoryInterface::class,
            BookingHostRepository::class
        );

        // ── Handover (استلام/تسليم السيارة) ← جديد ─────────────────────────────
        $this->app->bind(
            HandoverHostRepositoryInterface::class,
            HandoverHostRepository::class
        );

        $this->app->bind(
            HandoverGuestRepositoryInterface::class,
            HandoverGuestRepository::class
        );

        // ── Coupons ──────────────────────────────────────────────────────────
        $this->app->bind(
            CouponHostRepositoryInterface::class,
            CouponHostRepository::class
        );

        $this->app->bind(
            CouponGuestRepositoryInterface::class,
            CouponGuestRepository::class
        );
          // ── Reviews (تقييم الغيست للهوست) ← جديد ───────────────────────────────
        $this->app->bind(
            ReviewGuestRepositoryInterface::class,
            ReviewGuestRepository::class
        );

        $this->app->bind(
            ReviewHostRepositoryInterface::class,
            ReviewHostRepository::class
        );

        // ── Favorite ─────────────────────────────────────────────────────────
        $this->app->bind(
            FavoriteRepositoryInterface::class,
            FavoriteRepository::class
        );

        // ── Feature ──────────────────────────────────────────────────────────
        $this->app->bind(
            FeatureRepositoryInterface::class,
            FeatureRepository::class
        );

        // ── Vehicle ──────────────────────────────────────────────────────────
        $this->app->bind(
            VehicleAdminRepositoryInterface::class,
            VehicelAdminRepository::class
        );

        $this->app->bind(
            VehicleCommandRepositoryInterface::class,
            VehicelCommandRepository::class
        );

        $this->app->bind(
            VehicleQueryRepositoryInterface::class,
            VehicelQueryRepository::class
        );

        $this->app->bind(
            AdminStatisticsRepositoryInterface::class,
            AdminStatisticsRepository::class
        );

         // ── Account (سويتش الحساب) ← جديد ───────────────────────────────────────
        $this->app->bind(
            AccountSwitchRepositoryInterface::class,
            AccountSwitchRepository::class
        );

         // ── LocationTracking (تتبع موقع التوصيل اللحظي) ← جديد ──────────────────
        $this->app->bind(
            LocationTrackingHostRepositoryInterface::class,
            LocationTrackingHostRepository::class
        );

        $this->app->bind(
            LocationTrackingGuestRepositoryInterface::class,
            LocationTrackingGuestRepository::class
        );

        // ── Coupons ──────────────────────────────────────────────────────────
        $this->app->bind(
            CouponHostRepositoryInterface::class,
            CouponHostRepository::class
        );

        $this->app->bind(
            CouponGuestRepositoryInterface::class,
            CouponGuestRepository::class
        );
    }

    public function boot(): void
    {
        //
    }
}
