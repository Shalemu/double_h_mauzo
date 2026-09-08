<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Existing rows may reference a shop that was deleted directly in the
        // database (there was no cascade). Null those out first so the new
        // foreign key constraint can be added without failing.
        DB::statement('UPDATE products SET shop_id = NULL WHERE shop_id IS NOT NULL AND shop_id NOT IN (SELECT id FROM shops)');

        Schema::table('products', function (Blueprint $table) {
            $table->foreign('shop_id')->references('id')->on('shops')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropForeign(['shop_id']);
        });
    }
};
