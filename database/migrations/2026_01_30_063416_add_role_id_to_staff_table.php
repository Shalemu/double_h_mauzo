<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddRoleIdToStaffTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
public function up()
{
    Schema::table('staff', function (Blueprint $table) {
        $table->unsignedBigInteger('role_id')->after('shop_id');

        $table->foreign('role_id')
              ->references('id')
              ->on('roles')
              ->onDelete('cascade');

        $table->dropColumn('role'); // REMOVE old string column
    });
}

public function down()
{
    Schema::table('staff', function (Blueprint $table) {
        $table->string('role')->nullable();
        $table->dropForeign(['role_id']);
        $table->dropColumn('role_id');
    });
}

}
