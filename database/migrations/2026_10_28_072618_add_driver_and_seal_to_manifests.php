<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('manifests', function (Blueprint $table) {
            if (!Schema::hasColumn('manifests', 'driver_name')) {
                $table->string('driver_name')->nullable()->after('vehicle_no');
            }
            if (!Schema::hasColumn('manifests', 'seal_number')) {
                $table->string('seal_number')->nullable()->after('driver_name');
            }
        });
    }

    public function down(): void
    {
        Schema::table('manifests', function (Blueprint $table) {
            $table->dropColumn(['driver_name', 'seal_number']);
        });
    }
};
