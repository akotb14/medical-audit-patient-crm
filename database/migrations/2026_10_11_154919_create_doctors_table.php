<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('doctors', function (Blueprint $table) {
            $table->id();
            $table->string('fullname', 255);
            $table->string('phone', 15)->nullable();
            $table->string('email', 255)->nullable()->unique();
            $table->string('specialty', 255)->nullable();

            $table->foreignId('clinic_id')->nullable()
                ->constrained('clinics')->nullOnDelete();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('doctors');
    }
};