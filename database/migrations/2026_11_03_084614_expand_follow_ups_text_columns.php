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
        /*
        | The create migration originally limited these columns to the default
        | 255 characters while the follow-up rules accept 500. Growing the
        | columns in a forward migration keeps databases that already ran the
        | create migration in line without editing migration history.
        */
        Schema::table('follow_ups', function (Blueprint $table) {
            $table->string('reason', 500)->change();
            $table->string('outcome_note', 500)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        /*
        | Shrinking the columns back may fail or truncate when rows longer than
        | 255 characters exist. Rolling back therefore requires trimming those
        | values first on MySQL in strict mode.
        */
        Schema::table('follow_ups', function (Blueprint $table) {
            $table->string('reason', 255)->change();
            $table->string('outcome_note', 255)->nullable()->change();
        });
    }
};
