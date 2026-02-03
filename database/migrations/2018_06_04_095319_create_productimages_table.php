<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProductimagesTable extends Migration
{
    public function up()
    {
        Schema::create('productimages', function (Blueprint $table) {
            $table->increments('product_image_id');  // only auto-increment PK
            $table->unsignedInteger('product_id');   // normal integer
            $table->unsignedInteger('image_type');   // normal integer
            $table->string('product_image');         // varchar

            // Optional timestamps
            // $table->timestamps();

            // Optional foreign key
            // $table->foreign('product_id')->references('product_id')->on('products')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('productimages');
    }
}
