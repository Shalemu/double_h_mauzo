<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * financial_years — the business's accounting years (Settings › Financial
 * Years). Exactly one is "current": the business operates within it, it is
 * shown in the sidebar, and report filters offer it as a date range.
 *
 * Starts with FY 2026 (1 Jan – 31 Dec 2026) as current; edit it if the
 * business uses another year (e.g. July – June).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('financial_years', function (Blueprint $table) {
            $table->id();
            $table->string('name', 50)->unique();
            $table->date('start_date');
            $table->date('end_date');
            $table->boolean('is_current')->default(false)->index();
            $table->timestamps();
        });

        DB::table('financial_years')->insert([
            'name' => 'FY 2026',
            'start_date' => '2026-01-01',
            'end_date' => '2026-12-31',
            'is_current' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('financial_years');
    }
};
