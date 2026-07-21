<?php

use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        // processed_by column and foreign key already exist
    }

    public function down(): void
    {
        // Nothing to rollback
    }
};