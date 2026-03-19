<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSaleReturnsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('sale_returns', function (Blueprint $table) {
            $table->id();

            $table->foreignId('shop_id')->constrained()->cascadeOnDelete();
            $table->foreignId('sale_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();

            // Who processed the return
            $table->foreignId('staff_id')
                  ->nullable()
                  ->constrained('staff')
                  ->nullOnDelete();

            $table->integer('quantity');
            $table->decimal('amount', 12, 2);

            $table->enum('sale_type', ['retail', 'wholesale', 'both'])->default('retail');

            $table->string('reason')->nullable();

            $table->timestamp('returned_at')->useCurrent();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::dropIfExists('sale_returns');
    }
}