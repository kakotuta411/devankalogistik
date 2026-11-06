<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shipments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('resi_no', 50)->unique();
            $table->uuid('package_id')->nullable();
            $table->uuid('origin_branch_id')->nullable();
            $table->uuid('destination_branch_id')->nullable();
            $table->string('sender_name')->nullable();
            $table->string('sender_phone', 20)->nullable();
            $table->text('sender_address')->nullable();
            $table->string('receiver_name')->nullable();
            $table->string('receiver_phone', 20)->nullable();
            $table->text('receiver_address')->nullable();
            $table->integer('total_koli')->nullable();
            $table->decimal('total_weight', 10, 2)->nullable();
            $table->string('service_type', 50)->nullable();
            $table->string('shipment_status', 30)->default('Created');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shipments');
    }
};
