<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPaymentDetailsToSalesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
   public function up()
{
    Schema::table('sales', function (Blueprint $table) {
        $table->string('payment_type')->nullable(); // CRDB, MPESA, etc
        $table->decimal('received_amount', 15, 2)->nullable();
        $table->decimal('remaining_amount', 15, 2)->nullable();
        $table->decimal('change_amount', 15, 2)->nullable();
    });
}

public function down()
{
    Schema::table('sales', function (Blueprint $table) {
        $table->dropColumn([
            'payment_type',
            'received_amount',
            'remaining_amount',
            'change_amount'
        ]);
    });
}

}
