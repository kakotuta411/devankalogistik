<?php
namespace App\Services;

use App\Models\Invoice;
use Illuminate\Support\Facades\DB;

class InvoiceGenerator
{
    public static function generate(): string
    {
        return DB::transaction(function () {
            $year = now()->year;
            $month = now()->format('m');
            $prefix = 'DLI-INV';

            $max = Invoice::withoutGlobalScopes()
                ->where('invoice_no', 'like', $prefix . '-%')
                ->whereYear('created_at', $year)
                ->whereMonth('created_at', $month)
                ->lockForUpdate()
                ->get()
                ->max('invoice_no');

            $seq = 1;
            if ($max) {
                $parts = explode('-', $max);
                // Format: AKL-INV-YYYYMM-XXXXX (4 parts) or AKL-INV-YYYY-MM-XXXXX (5 parts)
                if (count($parts) === 4 && is_numeric($parts[3])) {
                    $seq = (int) $parts[3] + 1;
                } elseif (count($parts) === 5 && is_numeric($parts[4])) {
                    $seq = (int) $parts[4] + 1;
                }
            }

            return sprintf('%s-%s%s-%05d', $prefix, $year, $month, $seq);
        });
    }
}
