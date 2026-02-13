<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductsTable extends Migration
{
    public function up()
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id(); // <- bigInt unsigned, matches foreignId()
            $table->string('name');
            $table->string('item_code')->nullable();
            $table->string('barcode')->nullable();
            $table->string('barcode_symbology')->nullable();
            $table->text('description')->nullable();
            $table->string('discount_type')->nullable();
            $table->string('discount_value')->nullable();
            $table->integer('product_type')->default(0);
            $table->string('brand')->nullable();
            $table->unsignedBigInteger('category_id')->nullable();
            $table->unsignedBigInteger('subcategory_id')->nullable();
            $table->unsignedBigInteger('unit_id');
            $table->integer('min_quantity')->default(0);
            $table->integer('quantity')->default(0);
            $table->decimal('purchase_price', 15, 2)->default(0);
            $table->decimal('selling_price', 15, 2)->default(0);
            $table->string('invoice_number')->nullable();
            $table->date('expire_date')->nullable();
            $table->string('size')->nullable();
            $table->string('color')->nullable();
            $table->string('image')->nullable();
            $table->unsignedBigInteger('store_id')->nullable();
            $table->unsignedBigInteger('shop_id')->nullable();
            $table->unsignedBigInteger('admin_id');
            $table->string('selling_type')->nullable();
            $table->tinyInteger('sync_status')->default(0);

            $table->timestamps();
            $table->softDeletes();

            $table->engine = 'InnoDB';
        });
    }

    public function down()
    {
        Schema::dropIfExists('products');
    }
}


