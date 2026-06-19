<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

use App\Repositories\Interfaces\AuthRepositoryInterface;
use App\Repositories\AuthRepository;

use App\Repositories\Interfaces\VehicleRepositoryInterface;
use App\Repositories\VehicleRepository;

class RepositoryServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(AuthRepositoryInterface::class,    AuthRepository::class);
        $this->app->bind(VehicleRepositoryInterface::class, VehicleRepository::class);
    }

    public function boot(): void
    {
        //
    }
}
