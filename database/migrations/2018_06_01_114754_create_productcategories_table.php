<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProductcategoriesTable extends Migration
{
    public function up()
    {
        Schema::create('productcategories', function (Blueprint $table) {
            $table->increments('product_category_id'); // Only auto-increment PK
            $table->unsignedInteger('product_id');
            $table->unsignedInteger('category_id');

            // Optional: add foreign keys
            // $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
            // $table->foreign('category_id')->references('id')->on('categories')->onDelete('cascade');

            // Optional: add timestamps
            // $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('productcategories');
    }
}

