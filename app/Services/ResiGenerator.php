<?php
namespace App\Services;

use App\Models\Shipment;
use Illuminate\Support\Facades\DB;

class ResiGenerator
{
    public static function generate(): string
    {
        return DB::transaction(function () {
            // Count ALL shipments (not just today) so the sequence never collides
            // with a resi_no from a previous day. Lock rows to prevent race condition.
            $totalCount = Shipment::withoutGlobalScopes()
                ->lockForUpdate()
                ->get()
                ->count();
            $sequence = str_pad((string) ($totalCount + 1), 5, '0', STR_PAD_LEFT);
            return "DLI-{$sequence}";
        });
    }
}
