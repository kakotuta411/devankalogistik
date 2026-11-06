<?php

use App\Console\Commands\SyncRegionsCommand;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Daily region sync at 02:00 WIB
Schedule::command('regions:sync')->dailyAt('02:00');
