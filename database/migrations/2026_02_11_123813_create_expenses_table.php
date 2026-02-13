<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('shop_id');  // which shop this expense belongs to
            $table->unsignedBigInteger('user_id')->nullable(); // who created it
            $table->string('title');
            $table->decimal('amount', 15, 2);
            $table->text('note')->nullable();
            $table->timestamps();

            // Foreign key constraints
            $table->foreign('shop_id')->references('id')->on('shops')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('expenses');
    }
};
