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
        if (!Schema::hasColumn('sales', 'shop_id')) {
            Schema::table('sales', function (Blueprint $table) {
                $table->foreignId('shop_id')
                      ->nullable()
                      ->after('staff_id')
                      ->constrained('shops')
                      ->cascadeOnDelete();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        if (Schema::hasColumn('sales', 'shop_id')) {
            Schema::table('sales', function (Blueprint $table) {
                $table->dropForeign(['shop_id']);
                $table->dropColumn('shop_id');
            });
        }
    }
}