<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('branches', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('branch_code', 20)->unique();
            $table->string('name');
            $table->string('city')->nullable();
            $table->text('address')->nullable();
            $table->string('province_code', 10)->nullable();
            $table->string('regency_code', 10)->nullable();
            $table->string('district_code', 10)->nullable();
            $table->string('village_code', 15)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('branches');
    }
};
