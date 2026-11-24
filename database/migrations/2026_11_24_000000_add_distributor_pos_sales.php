<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Distributors sell from the POS while they are out with stock.
 *
 * - distribution_items.quantity_recorded: units of the line sold through the
 *   POS during the trip (each one a normal sale). What the distributor still
 *   carries = quantity_taken − quantity_recorded (− quantity_returned at settle).
 * - sales.distribution_id: the trip a sale was made on. Such sales are from the
 *   trip's store and do not take store stock again (it left when the trip was
 *   issued).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('distribution_items', function (Blueprint $table) {
            $table->unsignedInteger('quantity_recorded')->default(0)->after('quantity_taken');
        });

        Schema::table('sales', function (Blueprint $table) {
            $table->foreignId('distribution_id')->nullable()->after('shop_id')
                ->constrained('distributions')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->dropConstrainedForeignId('distribution_id');
        });
        Schema::table('distribution_items', function (Blueprint $table) {
            $table->dropColumn('quantity_recorded');
        });
    }
};
