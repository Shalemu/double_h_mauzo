<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class FixSalesStaffRelation extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
{
    Schema::table('sales', function (Blueprint $table) {
        // Remove wrong relation
        $table->dropForeign(['user_id']);
        $table->dropColumn('user_id');

        // Add correct one
        $table->foreignId('staff_id')
              ->after('id')
              ->constrained('staff')
              ->cascadeOnDelete();
    });
}

public function down()
{
    Schema::table('sales', function (Blueprint $table) {
        $table->dropForeign(['staff_id']);
        $table->dropColumn('staff_id');

        $table->foreignId('user_id')->constrained()->cascadeOnDelete();
    });
}

}
