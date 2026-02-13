<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddReceiptToExpensesTable extends Migration
{
    public function up(): void
    {
        Schema::table('expenses', function (Blueprint $table) {

            if (!Schema::hasColumn('expenses', 'receipt')) {
                $table->string('receipt')->nullable()->after('note');
            }

            if (!Schema::hasColumn('expenses', 'staff_id')) {
                $table->unsignedBigInteger('staff_id')->nullable()->after('user_id');
                $table->foreign('staff_id')
                      ->references('id')
                      ->on('staff')
                      ->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('expenses', function (Blueprint $table) {
            if (Schema::hasColumn('expenses', 'staff_id')) {
                $table->dropForeign(['staff_id']);
            }

            $table->dropColumn(array_filter([
                Schema::hasColumn('expenses', 'receipt') ? 'receipt' : null,
                Schema::hasColumn('expenses', 'staff_id') ? 'staff_id' : null,
            ]));
        });
    }
}
