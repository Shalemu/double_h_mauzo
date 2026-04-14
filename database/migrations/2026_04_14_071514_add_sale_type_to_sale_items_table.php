<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSaleTypeToSaleItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
public function up()
{
    Schema::table('sale_items', function (Blueprint $table) {
        $table->string('sale_type')->default('retail')->after('discount');
    });
}

public function down()
{
    Schema::table('sale_items', function (Blueprint $table) {
        $table->dropColumn('sale_type');
    });
}
}
