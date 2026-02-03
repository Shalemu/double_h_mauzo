<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProductassociatesTable extends Migration
{
    public function up()
    {
        Schema::create('productassociates', function (Blueprint $table) {
            $table->increments('associates_id');        // auto-increment PK
            $table->unsignedInteger('product_id');      // normal integer
            $table->unsignedInteger('product_associates_id'); // normal integer

            // Optional timestamps
            // $table->timestamps();

            // Optional foreign keys
            // $table->foreign('product_id')->references('product_id')->on('products')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('productassociates');
    }
}
