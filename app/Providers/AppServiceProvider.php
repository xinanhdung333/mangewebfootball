<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Helpers\Formatter;

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
        // Register helper functions
        if (!function_exists('formatCurrency')) {
            function formatCurrency($amount) {
                return Formatter::formatCurrency($amount);
            }
        }

        if (!function_exists('formatDateTime')) {
            function formatDateTime($datetime) {
                return Formatter::formatDateTime($datetime);
            }
        }
    }
}
