<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSaleTypeToProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
      public function up()
    {
        Schema::table('products', function (Blueprint $table) {
            $table->enum('sale_type', ['retail', 'wholesale', 'both'])
                  ->default('retail')
                  ->after('selling_price');
        });
    }

    public function down()
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('sale_type');
        });
    }
}
