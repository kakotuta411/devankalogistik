<?php

namespace App\Providers;

use App\Listeners\AuditLoginListener;
use App\View\Composers\PrintSettingComposer;
use Illuminate\Auth\Events\Login;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Event::listen(Login::class, AuditLoginListener::class);
        View::composer('print.*', PrintSettingComposer::class);
        \Illuminate\Pagination\Paginator::defaultView('pagination::bootstrap-5');
        \Illuminate\Pagination\Paginator::defaultSimpleView('pagination::bootstrap-5');

        // Rate limiting
        RateLimiter::for('login', function (Request $request) {
            return Limit::perMinute(5)->by($request->ip());
        });

        RateLimiter::for('tracking', function (Request $request) {
            return Limit::perMinute(30)->by($request->ip());
        });

        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });
    }
}