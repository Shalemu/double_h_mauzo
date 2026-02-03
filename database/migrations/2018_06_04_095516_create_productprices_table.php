<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProductpricesTable extends Migration
{
    public function up()
    {
        Schema::create('productprices', function (Blueprint $table) {
            $table->increments('product_price_id');    // auto-increment primary key
            $table->unsignedInteger('product_id');     // normal integer
            $table->string('quantity');
            $table->string('price');
            $table->string('special_price');
            $table->string('price_from_date');
            $table->string('price_to_date');
            $table->boolean('is_active')->default(1);  // normal boolean instead of auto-increment
            $table->timestamps();

            // Optional foreign key
            // $table->foreign('product_id')->references('product_id')->on('products')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('productprices');
    }
}
