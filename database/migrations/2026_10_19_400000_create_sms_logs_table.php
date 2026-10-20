<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * SMS module: one row per message sent to one number (Mobishastra).
 */
return new class extends Migration
{
    private array $permissions = ['sms.view', 'sms.send'];

    public function up()
    {
        Schema::create('sms_logs', function (Blueprint $table) {
            $table->id();
            $table->string('batch', 36)->index(); // groups one "Send" click
            $table->string('recipient');          // phone number, 255XXXXXXXXX
            $table->string('receiver')->nullable(); // name
            $table->string('type');               // customer | staff | custom
            $table->text('message');
            $table->unsignedInteger('sms_count')->default(1);
            $table->string('status')->default('pending'); // sent | failed
            $table->json('response')->nullable();
            $table->foreignId('sent_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        // Manager role (id 2) gets the module, like the other admin-panel modules.
        if (DB::table('roles')->where('id', 2)->exists()) {
            $existing = DB::table('role_permissions')->where('role_id', 2)->pluck('permission')->all();
            $now = now();
            DB::table('role_permissions')->insert(collect($this->permissions)->diff($existing)->map(fn ($key) => [
                'role_id' => 2, 'permission' => $key, 'created_at' => $now, 'updated_at' => $now,
            ])->values()->all());
        }
    }

    public function down()
    {
        DB::table('role_permissions')->whereIn('permission', $this->permissions)->delete();
        Schema::dropIfExists('sms_logs');
    }
};
