<?php

namespace App\Providers;

use App\Listeners\AuditLoginListener;
use App\View\Composers\PrintSettingComposer;
use Illuminate\Auth\Events\Login;
use Illuminate\Support\Facades\Event;
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
    }
}
