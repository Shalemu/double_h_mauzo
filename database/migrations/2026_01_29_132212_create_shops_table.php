<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shops', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('location');
            $table->unsignedBigInteger('admin_id'); // shop belongs to admin
            $table->unsignedBigInteger('user_id')->nullable(); // optional assigned employee
            $table->decimal('total_wages', 15, 2)->default(0);
            $table->decimal('capital', 15, 2)->default(0);
            $table->timestamps();

            // Foreign keys
            $table->foreign('admin_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shops');
    }
};
