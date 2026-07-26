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

use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // ── Auth ──────────────────────────────────────────────────────────────
        $this->app->bind(AuthRepositoryInterface::class, AuthRepository::class);

        // ── Vehicle ───────────────────────────────────────────────────────────
        $this->app->bind(VehicleQueryRepositoryInterface::class, VehicelQueryRepository::class);
        $this->app->bind(VehicleCommandRepositoryInterface::class, VehicelCommandRepository::class);
        $this->app->bind(VehicleAdminRepositoryInterface::class, VehicelAdminRepository::class);

        // ── Feature ───────────────────────────────────────────────────────────
        $this->app->bind(FeatureRepositoryInterface::class, FeatureRepository::class);

        // ── Favorite ──────────────────────────────────────────────────────────
        $this->app->bind(FavoriteRepositoryInterface::class, FavoriteRepository::class);

        // ── User Management ───────────────────────────────────────────────────
        $this->app->bind(UserAdminRepositoryInterface::class, UserAdminRepository::class);
        $this->app->bind(UserHostRepositoryInterface::class, UserHostRepository::class);
        $this->app->bind(UserGuestRepositoryInterface::class, UserGuestRepository::class);
        $this->app->bind(UserImageRepositoryInterface::class, UserImageRepository::class);

        // ── Complaints ────────────────────────────────────────────────────────
        $this->app->bind(ComplaintRepositoryInterface::class, ComplaintRepository::class);
        $this->app->bind(ComplaintAdminRepositoryInterface::class, ComplaintAdminRepository::class);

         $this->app->bind(ConversationRepositoryInterface::class,   ConversationRepository::class);
    }

    public function boot(): void
    {
        //
    }
}