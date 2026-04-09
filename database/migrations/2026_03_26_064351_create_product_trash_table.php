<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_trash', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('original_id'); // original product id
            $table->unsignedBigInteger('shop_id');
            $table->string('name');
            $table->string('brand')->nullable();
            $table->unsignedBigInteger('category_id')->nullable();
            $table->unsignedBigInteger('unit_id')->nullable();
            $table->integer('quantity')->nullable();
            $table->integer('min_quantity')->nullable();
            $table->decimal('purchase_price', 12, 2)->nullable();
            $table->decimal('selling_price', 12, 2)->nullable();
            $table->string('invoice_number')->nullable();
            $table->string('barcode')->nullable();
            $table->date('expire_date')->nullable();
            $table->string('size')->nullable();
            $table->string('color')->nullable();
            $table->string('sale_type')->nullable();
            $table->string('image')->nullable();
            $table->unsignedBigInteger('admin_id')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_trash');
    }
};
