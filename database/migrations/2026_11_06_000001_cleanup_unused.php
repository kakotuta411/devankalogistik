<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Remove po_id from invoices (unused) - only if exists
        if (Schema::hasColumn('invoices', 'po_id')) {
            Schema::table('invoices', function (Blueprint $table) {
                // Drop FK if exists (may not exist on fresh installs)
                try {
                    $table->dropForeign(['po_id']);
                } catch (\Exception $e) {
                    // FK doesn't exist, ignore
                }
                $table->dropColumn('po_id');
            });
        }

        // Drop purchase_orders table (unused - PO numbers stored as string on shipments)
        Schema::dropIfExists('purchase_orders');
    }

    public function down(): void
    {
        // Not reversible
    }
};
