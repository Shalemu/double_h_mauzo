<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('roles')->updateOrInsert(
            ['name' => 'Distributor'],
            ['name' => 'Distributor']
        );
    }

    public function down(): void
    {
        // Keep the role if staff have been assigned to it.
        $roleId = DB::table('roles')->where('name', 'Distributor')->value('id');
        if ($roleId && !DB::table('users')->where('role_id', $roleId)->exists()) {
            DB::table('roles')->where('id', $roleId)->delete();
        }
    }
};
