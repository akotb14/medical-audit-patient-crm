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
        Schema::create('patients', function (Blueprint $table) {
            $table->id();
            $table->string('fullname', 255);
            $table->string('phone', 15)->unique();
            $table->enum('gender', ['male', 'female']);
            $table->date('birthdate');
            $table->string('address', 255)->nullable();
            $table->enum('blood_group', ['A+', 'A-', 'B+', 'B-', 'O+', 'O-', 'AB+', 'AB-']);
            $table->string('allergies', 255)->nullable();
            $table->string('chronic_diseases', 255)->nullable();
            $table->string('emergency_name', 255)->nullable();
            $table->string('emergency_phone', 15)->nullable();
            $table->enum('status', ['active', 'inactive']);
            $table->foreignId('created_by')->constrained('users')->onDelete('restrict');
            $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('restrict');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('patients');
    }
};
