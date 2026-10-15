<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Admin-panel pages are now permission-checked. The manager role (id 2) had
 * full access before, so it starts with every admin-panel permission — admins
 * can trim it on Roles & Permissions. The admin role (id 1) needs none: it
 * always has full access.
 */
return new class extends Migration
{
    public function up()
    {
        if (!DB::table('roles')->where('id', 2)->exists()) {
            return;
        }

        $panelKeys = collect(config('permissions'))
            ->reject(fn ($perms, $module) => str_starts_with($module, 'POS '))
            ->flatMap(fn ($perms) => array_keys($perms));

        $existing = DB::table('role_permissions')->where('role_id', 2)->pluck('permission')->all();
        $now = now();

        DB::table('role_permissions')->insert($panelKeys->diff($existing)->map(fn ($key) => [
            'role_id' => 2,
            'permission' => $key,
            'created_at' => $now,
            'updated_at' => $now,
        ])->values()->all());
    }

    public function down()
    {
        $panelKeys = collect(config('permissions'))
            ->reject(fn ($perms, $module) => str_starts_with($module, 'POS '))
            ->flatMap(fn ($perms) => array_keys($perms))
            ->all();

        DB::table('role_permissions')->where('role_id', 2)->whereIn('permission', $panelKeys)->delete();
    }
};
