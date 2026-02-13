<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('expenses', function (Blueprint $table) {
            $table->unsignedBigInteger('created_by_id')->nullable()->after('shop_id');
            $table->string('created_by_type')->nullable()->after('created_by_id');

            // Optional: index for faster queries
            $table->index(['created_by_id', 'created_by_type']);
        });
    }

    public function down()
    {
        Schema::table('expenses', function (Blueprint $table) {
            $table->dropColumn(['created_by_id', 'created_by_type']);
        });
    }
};
