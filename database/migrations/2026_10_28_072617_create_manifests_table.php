<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('manifests', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('manifest_no', 50)->unique();
            $table->uuid('origin_branch_id')->nullable();
            $table->uuid('destination_branch_id')->nullable();
            $table->string('vehicle_no', 50)->nullable();
            $table->string('seal_number', 100)->nullable();
            $table->string('manifest_status', 20)->default('Draft');
            $table->timestamp('dispatch_time')->nullable();
            $table->timestamp('receive_time')->nullable();
            $table->string('driver_name')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('manifests');
    }
};
