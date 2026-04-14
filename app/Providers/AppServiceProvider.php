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
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if (config('app.env') === 'production') {
            \Illuminate\Support\Facades\URL::forceScheme('https');
        }

        // Register global storage_url() helper
        if (!function_exists('storage_url')) {
            function storage_url(?string $path): string {
                return \App\Helpers\StorageUrl::url($path);
            }
        }
    }
}
