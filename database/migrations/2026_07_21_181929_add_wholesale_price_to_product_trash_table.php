<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddWholesalePriceToProductTrashTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('product_trash', function (Blueprint $table) {
            $table->decimal('wholesale_price', 15, 2)->default(0)->after('selling_price');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('product_trash', function (Blueprint $table) {
            $table->dropColumn('wholesale_price');
        });
    }
}
