<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Archive patients instead of deleting them, so clinical history survives.
     *
     * Both columns are nullable and no existing row is touched: the inactive
     * patients already in the table stay exactly as they are.
     */
    public function up(): void
    {
        Schema::table('patients', function (Blueprint $table) {
            // `softDeletes()` only adds a nullable timestamp, it does not index it.
            $table->softDeletes();
            $table->index('deleted_at');

            $table->foreignId('deleted_by')
                ->nullable()
                ->after('deleted_at')
                ->constrained('users')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('patients', function (Blueprint $table) {
            $table->dropConstrainedForeignId('deleted_by');
            $table->dropIndex(['deleted_at']);
            $table->dropSoftDeletes();
        });
    }
};
