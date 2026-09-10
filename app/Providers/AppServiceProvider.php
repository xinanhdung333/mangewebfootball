<?php

namespace App\Providers;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\ServiceProvider;
use App\Helpers\Formatter;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\URL;

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
        Paginator::useBootstrap();
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });

        RateLimiter::for('login', function (Request $request) {
            return Limit::perMinute(5)->by(strtolower((string) $request->input('email')).'|'.$request->ip());
        });

 if (config('app.env') !== 'local') {
      //  URL::forceScheme('https');
    }
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


    // URL::forceScheme('https');

    }
}
