<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sale_returns', function (Blueprint $table) {

            // 1. drop old FK
            $table->dropForeign(['staff_id']);

            // 2. rename column
            $table->renameColumn('staff_id', 'processed_by');

            // 3. add new FK to users table (admin)
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

            $table->renameColumn('processed_by', 'staff_id');

            $table->foreign('staff_id')
                  ->references('id')
                  ->on('staff')
                  ->nullOnDelete();
        });
    }
};