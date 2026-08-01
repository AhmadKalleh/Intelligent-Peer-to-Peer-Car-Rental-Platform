<?php

namespace App\Providers;
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


use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    public function register(): void
    {
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

// ── Coupons ───────────────────────────────────────────────────────────
$this->app->bind(
    CouponHostRepositoryInterface::class,
    CouponHostRepository::class
);

$this->app->bind(
    CouponGuestRepositoryInterface::class,
    CouponGuestRepository::class
);
        $this->app->bind(
            NotificationRepositoryInterface::class,
            NotificationRepository::class
        );

    }

    public function boot(): void
    {
        //
    }
}
