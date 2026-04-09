<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePurchaseInvoicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
{
    Schema::create('purchase_invoices', function (Blueprint $table) {
        $table->id();

        $table->foreignId('shop_id')->constrained()->cascadeOnDelete();
        $table->foreignId('supplier_id')->constrained()->cascadeOnDelete();

        $table->string('invoice_number')->nullable();

        $table->decimal('total_amount', 12, 2);
        $table->decimal('amount_paid', 12, 2)->default(0);
        $table->decimal('remaining_amount', 12, 2);

        $table->timestamp('purchased_at')->useCurrent();

        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('purchase_invoices');
    }
}
