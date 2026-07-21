<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class MakeShopIdNotNullInSales extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Set existing NULL shop_id values to the default shop
        DB::table('sales')
            ->whereNull('shop_id')
            ->update(['shop_id' => 1]);

        // Make shop_id NOT NULL
        DB::statement('ALTER TABLE sales MODIFY shop_id BIGINT UNSIGNED NOT NULL');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement('ALTER TABLE sales MODIFY shop_id BIGINT UNSIGNED NULL');
    }
}