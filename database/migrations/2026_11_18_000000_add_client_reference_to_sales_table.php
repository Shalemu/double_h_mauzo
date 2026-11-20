<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * sales.client_reference — a unique id the mobile app generates for each sale
 * (e.g. a UUID). If the connection drops and the app sends the same sale
 * again, the server finds this reference and returns the existing sale
 * instead of recording it — and taking the stock — a second time.
 * The unique index makes this hold even for two requests at the same moment.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->string('client_reference', 64)->nullable()->unique()->after('id');
        });
    }

    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->dropUnique(['client_reference']);
            $table->dropColumn('client_reference');
        });
    }
};
