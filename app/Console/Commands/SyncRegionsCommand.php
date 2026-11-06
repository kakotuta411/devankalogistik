<?php

namespace App\Console\Commands;

use App\Services\RegionSyncService;
use Illuminate\Console\Command;

class SyncRegionsCommand extends Command
{
    protected $signature = 'regions:sync';
    protected $description = 'Sync regional data from emsifa.com API';

    public function handle(RegionSyncService $service): int
    {
        $this->info('Starting region sync...');
        $service->syncAll();
        $this->info('Region sync completed.');
        return 0;
    }
}
