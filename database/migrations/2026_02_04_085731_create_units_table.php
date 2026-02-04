<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('units', function (Blueprint $table) {
            $table->id();
            $table->string('name');          // full name of the unit
            $table->string('short_name');    // abbreviation, e.g., 'pc'
            $table->unsignedBigInteger('shop_id')->nullable();
            $table->unsignedBigInteger('admin_id')->nullable();
            $table->timestamps();

            // Optional: add foreign keys if shops/admins exist
            // $table->foreign('shop_id')->references('id')->on('shops')->onDelete('cascade');
            // $table->foreign('admin_id')->references('id')->on('admins')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('units');
    }
};
