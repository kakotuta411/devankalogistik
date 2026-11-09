<?php

namespace App\Console\Commands;

use App\Services\RegionSyncService;
use Illuminate\Console\Command;

class SyncRegionsCommand extends Command
{
    protected $signature = 'regions:sync {--force : Force re-sync all data including villages}';
    protected $description = 'Sync regional data from emsifa.com API (v2)';

    public function handle(RegionSyncService $service): int
    {
        $this->info('Starting region sync (emsifa v2)...');
        $service->syncAll($this->option('force'));
        $this->info('Region sync completed.');
        return 0;
    }
}
