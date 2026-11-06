<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('shipments', function (Blueprint $table) {
            $table->string('sender_province_code')->nullable()->after('sender_address');
            $table->string('sender_regency_code')->nullable()->after('sender_province_code');
            $table->string('sender_district_code')->nullable()->after('sender_regency_code');
            $table->string('sender_village_code')->nullable()->after('sender_district_code');
            $table->string('receiver_province_code')->nullable()->after('receiver_address');
            $table->string('receiver_regency_code')->nullable()->after('receiver_province_code');
            $table->string('receiver_district_code')->nullable()->after('receiver_regency_code');
            $table->string('receiver_village_code')->nullable()->after('receiver_district_code');
        });

        Schema::table('invoices', function (Blueprint $table) {
            $table->string('customer_province_code')->nullable()->after('customer_address');
            $table->string('customer_regency_code')->nullable()->after('customer_province_code');
            $table->string('customer_district_code')->nullable()->after('customer_regency_code');
            $table->string('customer_village_code')->nullable()->after('customer_district_code');
        });
    }

    public function down(): void
    {
        Schema::table('shipments', function (Blueprint $table) {
            $table->dropColumn([
                'sender_province_code', 'sender_regency_code',
                'sender_district_code', 'sender_village_code',
                'receiver_province_code', 'receiver_regency_code',
                'receiver_district_code', 'receiver_village_code',
            ]);
        });

        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn([
                'customer_province_code', 'customer_regency_code',
                'customer_district_code', 'customer_village_code',
            ]);
        });
    }
};
