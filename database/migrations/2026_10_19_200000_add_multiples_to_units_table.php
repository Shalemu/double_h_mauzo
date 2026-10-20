<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * A unit can be registered as a multiple of a base unit, e.g.
 * 1 Carton = 24 Piece (base_unit_id = Piece, multiplier = 24).
 */
return new class extends Migration
{
    public function up()
    {
        Schema::table('units', function (Blueprint $table) {
            $table->boolean('allow_decimal')->default(false)->after('short_name');
            $table->foreignId('base_unit_id')->nullable()->after('allow_decimal')->constrained('units')->nullOnDelete();
            $table->unsignedInteger('multiplier')->nullable()->after('base_unit_id');
        });
    }

    public function down()
    {
        Schema::table('units', function (Blueprint $table) {
            $table->dropForeign(['base_unit_id']);
            $table->dropColumn(['allow_decimal', 'base_unit_id', 'multiplier']);
        });
    }
};
