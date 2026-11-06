<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            if (!Schema::hasColumn('invoices', 'customer_id')) {
                $table->uuid('customer_id')->nullable()->after('branch_id');
                $table->foreign('customer_id')->references('id')->on('customers')->nullOnDelete();
            }
            if (!Schema::hasColumn('invoices', 'po_id')) {
                $table->uuid('po_id')->nullable()->after('customer_id');
                $table->foreign('po_id')->references('id')->on('purchase_orders')->nullOnDelete();
            }
            if (!Schema::hasColumn('invoices', 'packaging_cost')) {
                $table->decimal('packaging_cost', 15, 2)->default(0)->after('shipping_cost');
            }
            if (!Schema::hasColumn('invoices', 'equipment_charge')) {
                $table->decimal('equipment_charge', 15, 2)->default(0)->after('packaging_cost');
            }
            if (!Schema::hasColumn('invoices', 'ppftz03')) {
                $table->decimal('ppftz03', 15, 2)->default(0)->after('equipment_charge');
            }
            if (!Schema::hasColumn('invoices', 'ppn')) {
                $table->decimal('ppn', 15, 2)->default(0)->after('ppftz03');
            }
            if (!Schema::hasColumn('invoices', 'pph')) {
                $table->decimal('pph', 15, 2)->default(0)->after('ppn');
            }
            if (!Schema::hasColumn('invoices', 'discount_type')) {
                $table->string('discount_type')->nullable()->after('pph');
            }
            if (!Schema::hasColumn('invoices', 'discount_value')) {
                $table->decimal('discount_value', 15, 2)->default(0)->after('discount_type');
            }
            if (!Schema::hasColumn('invoices', 'other_costs')) {
                $table->decimal('other_costs', 15, 2)->default(0)->after('discount_value');
            }
            if (!Schema::hasColumn('invoices', 'amount_paid')) {
                $table->decimal('amount_paid', 15, 2)->default(0)->after('other_costs');
            }
            if (!Schema::hasColumn('invoices', 'total_amount')) {
                $table->decimal('total_amount', 15, 2)->default(0)->after('amount_paid');
            }
        });
    }

    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $cols = ['customer_id', 'po_id', 'packaging_cost', 'equipment_charge',
                     'ppftz03', 'ppn', 'pph', 'discount_type', 'discount_value',
                     'other_costs', 'amount_paid', 'total_amount'];
            foreach ($cols as $col) {
                if (Schema::hasColumn('invoices', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
