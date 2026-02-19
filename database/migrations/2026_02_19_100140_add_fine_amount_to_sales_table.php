<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFineAmountToSalesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
   public function up()
{
    Schema::table('sales', function ($table) {
        $table->decimal('fine_amount', 10, 2)->default(0);
    });
}



    /**
     * Reverse the migrations.
     *
     * @return void
     */
public function down()
{
    Schema::table('sales', function ($table) {
        $table->dropColumn('fine_amount');
    });
}
}
