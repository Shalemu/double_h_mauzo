<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('products', function (Blueprint $table) {
            $table->increments('id'); // primary key
            $table->string('name');
            $table->string('item_code')->nullable();
            $table->string('barcode')->nullable();
            $table->string('barcode_symbology')->nullable();
            $table->text('description')->nullable();
            $table->string('discount_type')->nullable();
            $table->string('discount_value')->nullable();
            $table->integer('product_type')->default(0); // or nullable if needed
            $table->string('brand')->nullable();
            $table->unsignedInteger('category_id')->nullable();
            $table->unsignedInteger('subcategory_id')->nullable();
            $table->unsignedInteger('unit_id');
            $table->integer('min_quantity')->default(0);
            $table->integer('quantity')->default(0);
            $table->decimal('purchase_price', 15, 2)->default(0);
            $table->decimal('selling_price', 15, 2)->default(0);
            $table->string('invoice_number')->nullable();
            $table->date('expire_date')->nullable();
            $table->string('size')->nullable();
            $table->string('color')->nullable();
            $table->string('image')->nullable();
            $table->unsignedInteger('store_id')->nullable();
            $table->unsignedInteger('shop_id')->nullable();
            $table->unsignedInteger('admin_id'); // logged in admin
            $table->string('selling_type')->nullable();
            $table->tinyInteger('sync_status')->default(0);

            $table->timestamps();
            $table->softDeletes();

            // Optional: foreign keys if you have categories, units, etc.
            // $table->foreign('category_id')->references('id')->on('product_categories')->onDelete('set null');
            // $table->foreign('subcategory_id')->references('id')->on('product_categories')->onDelete('set null');
            // $table->foreign('unit_id')->references('id')->on('units')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('products');
    }
}
