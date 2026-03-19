<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTotalAmountToPurchasesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
   public function up()
{
    Schema::table('purchases', function (Blueprint $table) {
        $table->decimal('total_amount', 15, 2)->default(0)->after('purchase_price');
    });
}

public function down()
{
    Schema::table('purchases', function (Blueprint $table) {
        $table->dropColumn('total_amount');
    });
}
}
