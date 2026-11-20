<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * payment_methods — the ways a customer can pay, managed in Settings ›
 * Payment Methods (e.g. "CRDB – 0150…", "Lipa na M-Pesa").
 *
 * Each method has a category (cash / mobile / bank / credit). Sales still
 * store that category in sales.payment_method, so credit/debt tracking and
 * the reports keep working; the method's name goes in sales.payment_type and
 * its id in sales.payment_method_id.
 *
 * Starts with the options the POS had built in.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_methods', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100)->unique();
            $table->string('category', 20)->index(); // cash | mobile | bank | credit
            $table->string('account_name', 150)->nullable();
            $table->string('account_number', 100)->nullable();
            $table->string('notes', 255)->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        $defaults = [
            ['Cash', 'cash'],
            ['M-Pesa', 'mobile'],
            ['Mixx by Yas', 'mobile'],
            ['Airtel Money', 'mobile'],
            ['HaloPesa', 'mobile'],
            ['CRDB', 'bank'],
            ['NMB', 'bank'],
            ['NBC', 'bank'],
            ['Equity', 'bank'],
            ['Credit', 'credit'],
        ];
        foreach ($defaults as $i => [$name, $category]) {
            DB::table('payment_methods')->insert([
                'name' => $name,
                'category' => $category,
                'is_active' => true,
                'sort_order' => ($i + 1) * 10,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        Schema::table('sales', function (Blueprint $table) {
            $table->foreignId('payment_method_id')->nullable()->after('payment_type')
                ->constrained('payment_methods')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->dropConstrainedForeignId('payment_method_id');
        });
        Schema::dropIfExists('payment_methods');
    }
};
