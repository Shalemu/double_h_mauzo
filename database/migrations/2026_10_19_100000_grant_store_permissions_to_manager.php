<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Stores became their own module with their own permissions. The manager
 * role (id 2) gets them, matching its access to shops.
 */
return new class extends Migration
{
    private array $permissions = ['stores.view', 'stores.create', 'stores.edit', 'stores.delete'];

    public function up()
    {
        if (!DB::table('roles')->where('id', 2)->exists()) {
            return;
        }

        $existing = DB::table('role_permissions')->where('role_id', 2)->pluck('permission')->all();
        $now = now();

        DB::table('role_permissions')->insert(collect($this->permissions)->diff($existing)->map(fn ($key) => [
            'role_id' => 2,
            'permission' => $key,
            'created_at' => $now,
            'updated_at' => $now,
        ])->values()->all());
    }

    public function down()
    {
        DB::table('role_permissions')->whereIn('permission', $this->permissions)->delete();
    }
};
