<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->decimal('total_bill', 18, 2);
            $table->string('payment_mode');
            $table->string('phone')->nullable();
            $table->string('delivery_mode')->nullable();
            $table->text('description')->nullable();
            $table->decimal('discount_value', 8, 2)->nullable();
            $table->decimal('discount', 18, 2)->nullable();
            $table->string('tax_percentage')->nullable();
            $table->decimal('gst_tax', 18, 2)->nullable();
            $table->decimal('shipping_value', 8, 2)->nullable();
            $table->decimal('shipping', 18, 2)->nullable();
            $table->decimal('due', 18, 2)->nullable();
            $table->decimal('amount_change', 10, 2)->nullable();
            $table->decimal('total_payable', 18, 2)->nullable();
            $table->decimal('sub_total', 18, 2)->nullable();
            $table->string('transaction_id');
            $table->string('status')->default('pending');
            $table->foreignId('sold_by')->constrained('users')->onDelete('cascade');
            $table->foreignId('supplied_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('sold_to')->nullable()->constrained('users')->onDelete('set null');
            $table->softDeletes(); // deleted_at
            $table->timestamps();
            $table->boolean('sync_status')->default(false);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
