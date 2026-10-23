<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Purchases become orders that are confirmed on delivery: a new purchase
 * is "pending" (no stock, not counted anywhere) until someone receives it,
 * entering what actually arrived and at what price. Existing purchases are
 * already in stock, so they become "received".
 *
 * Also grants the new purchases.receive permission to every role that can
 * already create purchases.
 */
return new class extends Migration
{
    public function up()
    {
        Schema::table('purchase_invoices', function (Blueprint $table) {
            $table->string('status', 20)->default('received')->after('payment_type')->index();
            $table->timestamp('ordered_at')->nullable()->after('status');
            $table->timestamp('received_at')->nullable()->after('ordered_at');
            $table->unsignedBigInteger('received_by')->nullable()->after('received_at');
        });

        Schema::table('purchase_items', function (Blueprint $table) {
            $table->integer('ordered_quantity')->nullable()->after('product_id');
            $table->decimal('selling_price', 15, 2)->nullable()->after('purchase_price');
            $table->decimal('wholesale_price', 15, 2)->nullable()->after('selling_price');
        });

        DB::table('purchase_invoices')->update([
            'ordered_at' => DB::raw('purchased_at'),
            'received_at' => DB::raw('purchased_at'),
        ]);
        DB::table('purchase_items')->update(['ordered_quantity' => DB::raw('quantity')]);

        $roles = DB::table('role_permissions')->where('permission', 'purchases.create')->pluck('role_id');
        $has = DB::table('role_permissions')->where('permission', 'purchases.receive')->pluck('role_id');
        $now = now();
        DB::table('role_permissions')->insert($roles->diff($has)->map(fn ($roleId) => [
            'role_id' => $roleId,
            'permission' => 'purchases.receive',
            'created_at' => $now,
            'updated_at' => $now,
        ])->values()->all());
    }

    public function down()
    {
        DB::table('role_permissions')->where('permission', 'purchases.receive')->delete();

        // Orders never received have no stock behind them.
        DB::table('purchase_invoices')->where('status', '!=', 'received')->delete();

        Schema::table('purchase_items', function (Blueprint $table) {
            $table->dropColumn(['ordered_quantity', 'selling_price', 'wholesale_price']);
        });

        Schema::table('purchase_invoices', function (Blueprint $table) {
            $table->dropIndex(['status']);
            $table->dropColumn(['status', 'ordered_at', 'received_at', 'received_by']);
        });
    }
};
