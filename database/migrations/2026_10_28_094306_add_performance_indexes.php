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
        Schema::table('visits', function (Blueprint $table) {
            $table->index(
                ['visit_status', 'date'],
                'visits_status_date_idx'
            );

            $table->index(
                'date',
                'visits_date_idx'
            );
        });

        Schema::table('follow_ups', function (Blueprint $table) {
            $table->index(
                ['status', 'due_date'],
                'followups_status_due_idx'
            );
        });

        Schema::table('complaints', function (Blueprint $table) {
            $table->index(
                ['status', 'created_at'],
                'complaints_status_created_idx'
            );
        });

        Schema::table('surveys', function (Blueprint $table) {
            $table->index(
                ['rating', 'created_at'],
                'surveys_rating_created_idx'
            );
        });

        Schema::table('patients', function (Blueprint $table) {
            $table->index('status', 'patients_status_idx');
        });

        Schema::table('doctors', function (Blueprint $table) {
            $table->index('status', 'doctors_status_idx');
        });

        Schema::table('clinics', function (Blueprint $table) {
            $table->index('status', 'clinics_status_idx');
        });
    }

    public function down(): void
    {
        Schema::table('visits', function (Blueprint $table) {
            $table->dropIndex('visits_status_date_idx');
            $table->dropIndex('visits_date_idx');
        });

        Schema::table('follow_ups', function (Blueprint $table) {
            $table->dropIndex('followups_status_due_idx');
        });

        Schema::table('complaints', function (Blueprint $table) {
            $table->dropIndex('complaints_status_created_idx');
        });

        Schema::table('surveys', function (Blueprint $table) {
            $table->dropIndex('surveys_rating_created_idx');
        });

        Schema::table('patients', function (Blueprint $table) {
            $table->dropIndex('patients_status_idx');
        });

        Schema::table('doctors', function (Blueprint $table) {
            $table->dropIndex('doctors_status_idx');
        });

        Schema::table('clinics', function (Blueprint $table) {
            $table->dropIndex('clinics_status_idx');
        });
    }
};
