<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('carts')) {
            Schema::create('carts', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->onDelete('cascade'); // staff or user
                $table->foreignId('product_id')->constrained()->onDelete('cascade'); // must match products.id
                $table->integer('quantity')->default(0);
                $table->decimal('discount', 10, 2)->default(0);
                $table->timestamps();

                $table->engine = 'InnoDB'; // ensure InnoDB
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('carts');
    }
};
