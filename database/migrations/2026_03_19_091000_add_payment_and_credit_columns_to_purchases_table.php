<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPaymentAndCreditColumnsToPurchasesTable extends Migration
{
    public function up()
    {
        Schema::table('purchases', function (Blueprint $table) {
            if (!Schema::hasColumn('purchases', 'remaining_credit')) {
                $table->decimal('remaining_credit', 15, 2)->default(0)->after('purchase_price');
            }
            if (!Schema::hasColumn('purchases', 'payment_type')) {
                $table->enum('payment_type', ['cash', 'credit'])->default('cash')->after('remaining_credit');
            }
        });
    }

    public function down()
    {
        Schema::table('purchases', function (Blueprint $table) {
            if (Schema::hasColumn('purchases', 'remaining_credit')) {
                $table->dropColumn('remaining_credit');
            }
            if (Schema::hasColumn('purchases', 'payment_type')) {
                $table->dropColumn('payment_type');
            }
        });
    }
}