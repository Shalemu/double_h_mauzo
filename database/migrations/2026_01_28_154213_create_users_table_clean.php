<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTableClean extends Migration
{
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id(); // Primary key

            // Names
            $table->string('first_name');
            $table->string('middle_name')->nullable();
            $table->string('last_name');
            $table->string('name');

            // Login
            $table->string('email')->unique();
            $table->string('username')->unique();
            $table->string('phone')->unique();
            $table->string('password');
            $table->rememberToken();

            // Verification & Roles
            $table->boolean('verified')->default(false);
            $table->timestamp('verified_at')->nullable();
            $table->unsignedTinyInteger('role_id')->default(0);
            $table->boolean('super_user')->default(false);
            $table->string('user_type')->default('Retailer');

            // System
            $table->integer('login_trials')->default(0);
            $table->boolean('password_reset')->default(false);
            $table->string('code')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->unsignedBigInteger('admin_id')->nullable();
            $table->tinyInteger('sync_status')->default(0);

            // Soft deletes & timestamps
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('users');
    }
}
