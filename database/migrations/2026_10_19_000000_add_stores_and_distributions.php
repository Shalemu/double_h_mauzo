<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Stores (warehouses) and distribution trips.
 *
 * A store is a shops row with type = 'store': it holds stock, receives
 * purchases and transfers, but never sells at the POS. Distributors take
 * stock out of a store on a trip; when they come back the returned units go
 * back into the store and the rest is recorded as a sale.
 */
return new class extends Migration
{
    private array $permissions = ['distributions.view', 'distributions.create', 'distributions.settle'];

    public function up()
    {
        Schema::table('shops', function (Blueprint $table) {
            $table->string('type')->default('shop')->after('name');
        });

        Schema::create('distributions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->constrained('shops')->cascadeOnDelete();
            $table->foreignId('staff_id')->constrained('staff')->cascadeOnDelete();
            $table->foreignId('issued_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('status')->default('out'); // out | settled
            $table->text('note')->nullable();
            $table->timestamp('settled_at')->nullable();
            $table->foreignId('settled_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('sale_id')->nullable()->constrained('sales')->nullOnDelete();
            $table->decimal('total_value', 15, 2)->default(0);
            $table->decimal('amount_received', 15, 2)->default(0);
            $table->decimal('balance', 15, 2)->default(0);
            $table->timestamps();
        });

        Schema::create('distribution_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('distribution_id')->constrained('distributions')->cascadeOnDelete();
            $table->unsignedBigInteger('product_id')->nullable();
            // Kept so history still reads correctly if a product is deleted later.
            $table->string('product_name');
            $table->integer('quantity_taken');
            $table->integer('quantity_returned')->default(0);
            $table->decimal('price', 15, 2)->default(0);
            $table->timestamps();
        });

        Schema::create('distribution_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('distribution_id')->constrained('distributions')->cascadeOnDelete();
            $table->decimal('amount', 15, 2);
            $table->foreignId('received_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('note')->nullable();
            $table->timestamps();
        });

        // Manager role (id 2) gets the new admin-panel permissions, as with the others.
        if (DB::table('roles')->where('id', 2)->exists()) {
            $existing = DB::table('role_permissions')->where('role_id', 2)->pluck('permission')->all();
            $now = now();
            DB::table('role_permissions')->insert(collect($this->permissions)->diff($existing)->map(fn ($key) => [
                'role_id' => 2,
                'permission' => $key,
                'created_at' => $now,
                'updated_at' => $now,
            ])->values()->all());
        }
    }

    public function down()
    {
        DB::table('role_permissions')->whereIn('permission', $this->permissions)->delete();

        Schema::dropIfExists('distribution_payments');
        Schema::dropIfExists('distribution_items');
        Schema::dropIfExists('distributions');

        Schema::table('shops', function (Blueprint $table) {
            $table->dropColumn('type');
        });
    }
};
