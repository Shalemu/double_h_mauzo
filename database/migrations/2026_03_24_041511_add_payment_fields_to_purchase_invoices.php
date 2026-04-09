<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('purchase_invoices', function (Blueprint $table) {

            if (!Schema::hasColumn('purchase_invoices', 'amount_paid')) {
                $table->decimal('amount_paid', 15, 2)->default(0)->after('total_amount');
            }

            if (!Schema::hasColumn('purchase_invoices', 'remaining_credit')) {
                $table->decimal('remaining_credit', 15, 2)->default(0)->after('amount_paid');
            }

        });
    }

    public function down(): void
    {
        Schema::table('purchase_invoices', function (Blueprint $table) {
            if (Schema::hasColumn('purchase_invoices', 'amount_paid')) {
                $table->dropColumn('amount_paid');
            }
            if (Schema::hasColumn('purchase_invoices', 'remaining_credit')) {
                $table->dropColumn('remaining_credit');
            }
        });
    }
};