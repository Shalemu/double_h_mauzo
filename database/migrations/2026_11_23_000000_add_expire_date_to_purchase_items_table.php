<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * purchase_items.expire_date — the expiry date of the goods that arrived on
 * this line, entered when the order is received. Keeps each delivery's
 * expiry on record; the product itself shows the earliest one in stock.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('purchase_items', function (Blueprint $table) {
            $table->date('expire_date')->nullable()->after('wholesale_price');
        });
    }

    public function down(): void
    {
        Schema::table('purchase_items', function (Blueprint $table) {
            $table->dropColumn('expire_date');
        });
    }
};
