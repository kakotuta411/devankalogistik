<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->uuid('parent_invoice_id')->nullable()->after('po_id');
            $table->foreign('parent_invoice_id')->references('id')->on('invoices')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropForeign(['parent_invoice_id']);
            $table->dropColumn('parent_invoice_id');
        });
    }
};
