<?php

namespace App\Providers;

use App\Repositories\Auth\Interfaces\AuthRepositoryInterface;
use App\Repositories\Feature\Interfaces\FeatureRepositoryInterface;
use App\Repositories\Vehicle\Interfaces\VehicleAdminRepositoryInterface;
use App\Repositories\Vehicle\Interfaces\VehicleCommandRepositoryInterface;
use App\Repositories\Vehicle\Interfaces\VehicleQueryRepositoryInterface;


use App\Repositories\Auth\AuthRepository;
use App\Repositories\Feature\FeatureRepository;
use App\Repositories\Vehicle\VehicelAdminRepository;
use App\Repositories\Vehicle\VehicelCommandRepository;
use App\Repositories\Vehicle\VehicelQueryRepository;


use Illuminate\Support\ServiceProvider;


class RepositoryServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(AuthRepositoryInterface::class,    AuthRepository::class);
        $this->app->bind(VehicleQueryRepositoryInterface::class, VehicelQueryRepository::class);
        $this->app->bind(VehicleCommandRepositoryInterface::class, VehicelCommandRepository::class);
        $this->app->bind(VehicleAdminRepositoryInterface::class, VehicelAdminRepository::class);
        $this->app->bind(FeatureRepositoryInterface::class, FeatureRepository::class);
    }

    public function boot(): void
    {
        //
    }
}
