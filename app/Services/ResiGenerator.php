<?php
namespace App\Services;

use App\Models\Shipment;
use Carbon\Carbon;

class ResiGenerator
{
    public static function generate(): string
    {
        $date = now()->format('Ymd');
        $dailyCount = Shipment::withoutGlobalScopes()
            ->whereDate('created_at', now()->toDateString())
            ->lockForUpdate()
            ->get()
            ->count();
        $sequence = str_pad((string) ($dailyCount + 1), 5, '0', STR_PAD_LEFT);
        return "AKL-{$sequence}";
    }
}
