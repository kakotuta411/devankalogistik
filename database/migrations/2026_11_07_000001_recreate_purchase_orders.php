<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('purchase_orders', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('po_number')->unique();
            $table->foreignUuid('customer_id')->nullable()->constrained('customers')->nullOnDelete();
            $table->decimal('total_amount', 15, 2)->default(0);
            $table->decimal('paid_amount', 15, 2)->default(0);
            $table->string('status')->default('draft'); // draft, active, paid, partial, cancelled
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // Re-add po_id to invoices
        if (!Schema::hasColumn('invoices', 'po_id')) {
            Schema::table('invoices', function (Blueprint $table) {
                $table->foreignUuid('po_id')->nullable()->constrained('purchase_orders')->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('invoices', 'po_id')) {
            Schema::table('invoices', function (Blueprint $table) {
                $table->dropForeign(['po_id']);
                $table->dropColumn('po_id');
            });
        }
        Schema::dropIfExists('purchase_orders');
    }
};