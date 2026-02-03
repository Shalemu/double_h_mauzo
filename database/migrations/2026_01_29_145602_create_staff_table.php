<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('staff', function (Blueprint $table) {
            $table->id();

            $table->string('first_name');
            $table->string('last_name');

            $table->string('phone')->unique();
            $table->string('email')->nullable();

            $table->unsignedBigInteger('shop_id');
            $table->string('role');

            $table->decimal('wages', 12, 2)->default(0);

            $table->string('password');

            $table->timestamps();

            $table->foreign('shop_id')
                  ->references('id')
                  ->on('shops')
                  ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('staff');
    }
};
