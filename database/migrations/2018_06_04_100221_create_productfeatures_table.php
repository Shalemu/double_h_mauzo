<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProductfeaturesTable extends Migration
{
    public function up()
    {
        Schema::create('productfeatures', function (Blueprint $table) {
            $table->increments('product_features_id'); // auto-increment PK
            $table->unsignedInteger('product_id');     // normal integer
            $table->unsignedInteger('features_id');    // normal integer

            // Optional timestamps
            // $table->timestamps();

            // Optional foreign keys
            // $table->foreign('product_id')->references('product_id')->on('products')->onDelete('cascade');
            // $table->foreign('features_id')->references('feature_id')->on('features')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('productfeatures');
    }
}
