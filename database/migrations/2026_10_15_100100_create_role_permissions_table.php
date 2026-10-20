<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('role_permissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('role_id')->constrained('roles')->onDelete('cascade');
            $table->string('permission', 100);
            $table->timestamps();
            $table->unique(['role_id', 'permission']);
        });

        // Existing roles keep every staff feature they had before
        // permissions existed; admins can trim them from the Roles screen.
        $keys = collect(config('permissions'))->flatMap(fn ($group) => array_keys($group));
        $now = now();
        foreach (DB::table('roles')->pluck('id') as $roleId) {
            DB::table('role_permissions')->insert($keys->map(fn ($key) => [
                'role_id' => $roleId,
                'permission' => $key,
                'created_at' => $now,
                'updated_at' => $now,
            ])->all());
        }
    }

    public function down()
    {
        Schema::dropIfExists('role_permissions');
    }
};
