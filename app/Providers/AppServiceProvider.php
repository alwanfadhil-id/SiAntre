<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Only register if the class exists
        if (class_exists(\SimpleSoftwareIO\QrCode\ServiceProvider::class)) {
            $this->app->register(\SimpleSoftwareIO\QrCode\ServiceProvider::class);
        }
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
