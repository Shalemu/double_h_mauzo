<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sales', function (Blueprint $table) {
            $table->id();

            // Staff who made the sale
            $table->foreignId('user_id')->constrained()->onDelete('cascade');

            // Customer (optional)
            $table->foreignId('customer_id')->nullable()
                  ->constrained('customers')->nullOnDelete();

            $table->decimal('bill_discount', 10, 2)->default(0);
            $table->decimal('total', 10, 2)->default(0);
            $table->string('payment_method')->default('mpesa');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sales');
    }
};
