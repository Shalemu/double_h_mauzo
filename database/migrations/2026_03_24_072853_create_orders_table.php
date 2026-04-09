<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('staff_id')->constrained('staff')->onDelete('cascade'); // staff who created the order
            $table->foreignId('shop_id')->constrained('shops')->onDelete('cascade'); // shop for the order
            $table->decimal('total_amount', 15, 2)->default(0); // total price of all items
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending'); // order status
            $table->text('note')->nullable(); // optional note
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('orders');
    }
};