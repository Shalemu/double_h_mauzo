<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddShopIdToSalesTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('sales', function (Blueprint $table) {
            // Add column nullable first to avoid FK errors
            $table->foreignId('shop_id')
                  ->nullable()
                  ->after('staff_id')
                  ->constrained('shops')
                  ->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('sales', function (Blueprint $table) {
            // Drop foreign key first
            $table->dropForeign(['shop_id']);

            // Then drop column
            $table->dropColumn('shop_id');
        });
    }
}
