<?php

namespace App\Providers;

use App\Interfaces\Services\OrderServiceInterface;
use App\Interfaces\Validators\OrderValidatorInterface;
use App\Services\OrderService;
use App\Validators\OrderValidator;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(OrderServiceInterface::class, OrderService::class);
        $this->app->bind(OrderValidatorInterface::class, OrderValidator::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
