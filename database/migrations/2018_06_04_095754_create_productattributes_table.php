<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProductattributesTable extends Migration
{
    public function up()
    {
        Schema::create('productattributes', function (Blueprint $table) {
            $table->increments('product_attribute_id'); // auto-increment PK
            $table->unsignedInteger('product_id');      // normal integer
            $table->unsignedInteger('attribute_id');    // normal integer

            // Optional: add foreign keys
            // $table->foreign('product_id')->references('product_id')->on('products')->onDelete('cascade');
            // $table->foreign('attribute_id')->references('attribute_id')->on('attributes')->onDelete('cascade');

            // Optional timestamps
            // $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('productattributes');
    }
}
