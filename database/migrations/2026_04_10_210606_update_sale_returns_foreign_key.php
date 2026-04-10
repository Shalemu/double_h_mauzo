<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('sale_returns', function (Blueprint $table) {

            // remove old FK if exists
            $table->dropForeign(['sale_id']);

            // correct FK
            $table->foreign('sale_id')
                ->references('id')
                ->on('sale_items')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('sale_returns', function (Blueprint $table) {

            $table->dropForeign(['sale_id']);

            $table->foreign('sale_id')
                ->references('id')
                ->on('sales')
                ->onDelete('cascade');
        });
    }
};

