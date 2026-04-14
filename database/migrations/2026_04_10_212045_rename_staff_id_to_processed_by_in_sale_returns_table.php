<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Add column if not exists
        Schema::table('sale_returns', function (Blueprint $table) {
            if (!Schema::hasColumn('sale_returns', 'processed_by')) {
                $table->unsignedBigInteger('processed_by')
                      ->nullable()
                      ->after('staff_id');
            }
        });

        // Copy data ONLY if old column exists
        if (Schema::hasColumn('sale_returns', 'staff_id')) {
            DB::statement("
                UPDATE sale_returns
                SET processed_by = staff_id
            ");
        }

        // Add foreign key (clean, no try/catch)
        Schema::table('sale_returns', function (Blueprint $table) {
            $table->foreign('processed_by')
                  ->references('id')
                  ->on('users')
                  ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('sale_returns', function (Blueprint $table) {
            $table->dropForeign(['processed_by']);
            $table->dropColumn('processed_by');
        });
    }
};