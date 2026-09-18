<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // A sale can now be rung up directly by an admin (no staff row
        // involved), so staff_id can no longer be required.
        DB::statement('ALTER TABLE sales MODIFY staff_id BIGINT UNSIGNED NULL');

        Schema::table('sales', function (Blueprint $table) {
            $table->foreignId('admin_id')->nullable()->after('staff_id')
                ->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->dropForeign(['admin_id']);
            $table->dropColumn('admin_id');
        });

        DB::statement('ALTER TABLE sales MODIFY staff_id BIGINT UNSIGNED NOT NULL');
    }
};
