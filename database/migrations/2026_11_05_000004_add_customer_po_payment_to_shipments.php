<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('shipments', function (Blueprint $table) {
            $table->uuid('customer_id')->nullable()->after('resi_no');
            $table->foreign('customer_id')->references('id')->on('customers')->nullOnDelete();
            $table->string('po_number')->nullable()->after('customer_id');
            $table->string('payment_type')->default('kredit')->after('service_type'); // kredit, lunas, cod
        });
    }

    public function down(): void
    {
        Schema::table('shipments', function (Blueprint $table) {
            $table->dropForeign(['customer_id']);
            $table->dropColumn(['customer_id', 'po_number', 'payment_type']);
        });
    }
};
