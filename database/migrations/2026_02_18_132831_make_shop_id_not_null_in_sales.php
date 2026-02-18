<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
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
        // Set all existing shop_id to 1 (au shop default) if null
        DB::table('sales')->whereNull('shop_id')->update(['shop_id' => 1]);

        // Change column to NOT NULL using raw SQL
        DB::statement('ALTER TABLE sales MODIFY shop_id BIGINT UNSIGNED NOT NULL');

        // Add foreign key constraint if not already present
        DB::statement('ALTER TABLE sales ADD CONSTRAINT sales_shop_id_foreign FOREIGN KEY (shop_id) REFERENCES shops(id) ON DELETE CASCADE');
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
