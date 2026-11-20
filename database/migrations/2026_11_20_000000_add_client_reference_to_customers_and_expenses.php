<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * customers.client_reference and expenses.client_reference — a unique id the
 * mobile app makes for each customer / expense it records (also offline).
 * If the connection drops and the app sends the same one again, the API
 * returns the record already saved instead of creating a duplicate — the
 * same protection sales.client_reference gives sales.
 */
return new class extends Migration
{
    public function up(): void
    {
        foreach (['customers', 'expenses'] as $table) {
            Schema::table($table, function (Blueprint $t) {
                $t->string('client_reference', 64)->nullable()->unique()->after('id');
            });
        }
    }

    public function down(): void
    {
        foreach (['customers', 'expenses'] as $table) {
            Schema::table($table, function (Blueprint $t) {
                $t->dropUnique(['client_reference']);
                $t->dropColumn('client_reference');
            });
        }
    }
};
