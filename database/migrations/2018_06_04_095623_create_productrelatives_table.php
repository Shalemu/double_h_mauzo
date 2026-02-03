<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProductrelativesTable extends Migration
{
    public function up()
    {
        Schema::create('productrelatives', function (Blueprint $table) {
            $table->increments('product_relative_id'); // only auto-increment PK
            $table->unsignedInteger('product_id');      // normal integer
            $table->string('product_relative');
            $table->timestamps();

            // Optional foreign key
            // $table->foreign('product_id')->references('product_id')->on('products')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('productrelatives');
    }
}
