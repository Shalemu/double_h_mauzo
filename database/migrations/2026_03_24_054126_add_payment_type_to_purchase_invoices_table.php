<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPaymentTypeToPurchaseInvoicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
{
    Schema::table('purchase_invoices', function (Blueprint $table) {
        $table->string('payment_type')->default('credit'); // or 'cash'
    });
}

public function down()
{
    Schema::table('purchase_invoices', function (Blueprint $table) {
        $table->dropColumn('payment_type');
    });
}
}
