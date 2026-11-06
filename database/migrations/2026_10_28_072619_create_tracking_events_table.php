<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tracking_events', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('shipment_id')->nullable();
            $table->string('event_type', 50);
            $table->string('location', 255)->nullable();
            $table->text('notes')->nullable();
            $table->timestamp('event_time');
            $table->uuid('actor_id')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tracking_events');
    }
};
