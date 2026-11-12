<?php
namespace App\Services;

use App\Models\Manifest;
use Illuminate\Support\Facades\DB;

class ManifestGenerator
{
    public static function generate(): string
    {
        return DB::transaction(function () {
            $date = now()->format('Ymd');
            $dailyCount = Manifest::withoutGlobalScopes()
                ->whereDate('created_at', now()->toDateString())
                ->lockForUpdate()
                ->get()
                ->count();
            $sequence = str_pad((string) ($dailyCount + 1), 5, '0', STR_PAD_LEFT);
            return "DLI-MNF-{$date}-{$sequence}";
        });
    }
}
