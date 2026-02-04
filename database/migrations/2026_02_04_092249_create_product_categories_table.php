<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('product_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');                  // Category name
            $table->string('description')->nullable(); // Optional description
            $table->unsignedBigInteger('parent_id')->nullable(); // For subcategories
            $table->unsignedBigInteger('shop_id')->nullable();   // Optional shop
            $table->unsignedBigInteger('admin_id');             // Who created it
            $table->timestamps();
            $table->softDeletes(); // optional if you want soft deletes

            // Foreign keys (optional, add if you have shops table)
            // $table->foreign('shop_id')->references('id')->on('shops')->onDelete('cascade');
            // $table->foreign('admin_id')->references('id')->on('users')->onDelete('cascade');
            // $table->foreign('parent_id')->references('id')->on('product_categories')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_categories');
    }
};
