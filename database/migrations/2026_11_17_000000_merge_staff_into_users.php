<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Moves every row of `staff` into `users` (user_type = 'staff') so the app has
 * one login table. Every foreign key that pointed at `staff` is re-pointed at
 * `users`, and the morph columns (expenses.created_by_*, notifications) that
 * referenced App\Models\Staff are rewritten to App\Models\Users.
 *
 * The old table is kept as `staff_legacy` and users.legacy_staff_id keeps the
 * old id, so the move can be audited (and reversed with `down`).
 *
 * TAKE A FULL DATABASE BACKUP BEFORE RUNNING THIS ON PRODUCTION. MySQL DDL is
 * not transactional, so a failure half-way is recovered from the backup.
 */
return new class extends Migration
{
    private const STAFF_MORPH = 'App\\Models\\Staff';
    private const USERS_MORPH = 'App\\Models\\Users';

    /** [table, id column, type column] */
    private const MORPHS = [
        ['expenses', 'created_by_id', 'created_by_type'],
        ['notifications', 'notifiable_id', 'notifiable_type'],
    ];

    public function up(): void
    {
        if (!Schema::hasTable('staff')) {
            return; // already merged
        }

        $this->preflight();

        // 1. Columns staff need on users.
        Schema::table('users', function (Blueprint $table) {
            $table->string('email')->nullable()->change();          // staff email is optional
            $table->unsignedBigInteger('role_id')->default(0)->change(); // was tinyint
            $table->unsignedBigInteger('shop_id')->nullable()->after('role_id');
            $table->decimal('wages', 12, 2)->nullable()->after('shop_id');
            $table->unsignedBigInteger('legacy_staff_id')->nullable()->unique()->after('wages');

            $table->foreign('shop_id')->references('id')->on('shops')->nullOnDelete();

            // Phone / email are unique per account type, as they were with two
            // tables: a shop's POS (staff) login may share the business phone
            // with the owner's admin account. Staff log in by phone, admins by
            // email/username, and each login only looks at its own type.
            $table->dropUnique(['phone']);
            $table->dropUnique(['email']);
            $table->unique(['phone', 'user_type']);
            $table->unique(['email', 'user_type']);
        });

        // 2. Drop every FK that points at staff (found live, so it also covers
        //    anything production has that the migrations here don't show).
        $foreignKeys = $this->foreignKeysTo('staff');
        foreach ($foreignKeys as $fk) {
            Schema::table($fk->table_name, fn (Blueprint $t) => $t->dropForeign($fk->constraint_name));
        }

        // 3. Copy rows and re-map ids — all or nothing.
        DB::transaction(function () use ($foreignKeys) {
            $taken = DB::table('users')->pluck('username')->map(fn ($u) => strtolower($u))->flip()->all();

            foreach (DB::table('staff')->orderBy('id')->get() as $s) {
                $username = $this->uniqueUsername($s, $taken);
                $taken[strtolower($username)] = true;

                DB::table('users')->insert([
                    'first_name'      => $s->first_name,
                    'last_name'       => $s->last_name,
                    'name'            => trim($s->first_name . ' ' . $s->last_name),
                    'email'           => ($s->email ?? '') !== '' ? $s->email : null,
                    'username'        => $username,
                    'phone'           => $s->phone,
                    'password'        => $s->password, // already hashed
                    'verified'        => true,
                    'role_id'         => $s->role_id ?? 0,
                    'shop_id'         => $s->shop_id,
                    'wages'           => $s->wages,
                    'user_type'       => 'staff',
                    'legacy_staff_id' => $s->id,
                    'created_at'      => $s->created_at,
                    'updated_at'      => $s->updated_at,
                ]);
            }

            foreach ($foreignKeys as $fk) {
                DB::statement(
                    "UPDATE `{$fk->table_name}` x JOIN `users` u ON u.legacy_staff_id = x.`{$fk->column_name}`
                     SET x.`{$fk->column_name}` = u.id"
                );
            }

            foreach (self::MORPHS as [$table, $id, $type]) {
                if (Schema::hasTable($table)) {
                    DB::update(
                        "UPDATE `{$table}` x JOIN `users` u ON u.legacy_staff_id = x.`{$id}`
                         SET x.`{$id}` = u.id, x.`{$type}` = ?
                         WHERE x.`{$type}` = ?",
                        [self::USERS_MORPH, self::STAFF_MORPH]
                    );
                }
            }
        });

        // 4. Point the FKs at users. Restrict (not cascade) on delete: removing
        //    a person must never silently wipe their sales / orders history.
        //    Users soft-delete, so normal deletes never hit this.
        foreach ($foreignKeys as $fk) {
            $nullable = $this->isNullable($fk->table_name, $fk->column_name);
            Schema::table($fk->table_name, function (Blueprint $t) use ($fk, $nullable) {
                $foreign = $t->foreign($fk->column_name)->references('id')->on('users');
                $nullable ? $foreign->nullOnDelete() : $foreign->restrictOnDelete();
            });
        }

        // 5. Keep the old table for auditing; drop it in a later migration.
        Schema::rename('staff', 'staff_legacy');
    }

    public function down(): void
    {
        if (!Schema::hasTable('staff_legacy')) {
            return;
        }

        $newStaff = DB::table('users')->where('user_type', 'staff')->whereNull('legacy_staff_id')->count();
        if ($newStaff > 0) {
            throw new RuntimeException(
                "{$newStaff} staff were created after the merge and have no row in staff_legacy. "
                . 'Rolling back would lose them — restore from backup instead.'
            );
        }

        Schema::rename('staff_legacy', 'staff');

        // FKs that were moved are the staff_id columns now pointing at users.
        $foreignKeys = collect($this->foreignKeysTo('users'))->where('column_name', 'staff_id')->values();
        foreach ($foreignKeys as $fk) {
            Schema::table($fk->table_name, fn (Blueprint $t) => $t->dropForeign($fk->constraint_name));
        }

        DB::transaction(function () use ($foreignKeys) {
            foreach ($foreignKeys as $fk) {
                DB::statement(
                    "UPDATE `{$fk->table_name}` x JOIN `users` u ON u.id = x.`{$fk->column_name}`
                     SET x.`{$fk->column_name}` = u.legacy_staff_id
                     WHERE u.legacy_staff_id IS NOT NULL"
                );
            }

            foreach (self::MORPHS as [$table, $id, $type]) {
                if (Schema::hasTable($table)) {
                    DB::update(
                        "UPDATE `{$table}` x JOIN `users` u ON u.id = x.`{$id}`
                         SET x.`{$id}` = u.legacy_staff_id, x.`{$type}` = ?
                         WHERE x.`{$type}` = ? AND u.legacy_staff_id IS NOT NULL",
                        [self::STAFF_MORPH, self::USERS_MORPH]
                    );
                }
            }

            DB::table('users')->whereNotNull('legacy_staff_id')->delete();
        });

        foreach ($foreignKeys as $fk) {
            Schema::table($fk->table_name, fn (Blueprint $t) => $t->foreign($fk->column_name)
                ->references('id')->on('staff')->cascadeOnDelete());
        }

        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['shop_id']);
            $table->dropUnique(['legacy_staff_id']);
            $table->dropColumn(['shop_id', 'wages', 'legacy_staff_id']);

            $table->dropUnique(['phone', 'user_type']);
            $table->dropUnique(['email', 'user_type']);
            $table->unique('phone');
            $table->unique('email');
        });
    }

    /**
     * Stop before touching anything if staff and users can't be merged cleanly.
     */
    private function preflight(): void
    {
        // Clashes with admin accounts are fine (uniqueness is per user_type);
        // clashes between two staff are not. Phone is already unique in staff.
        $problems = [];

        $existingStaff = DB::table('users')->where('user_type', 'staff')->count();
        if ($existingStaff > 0) {
            $problems[] = "{$existingStaff} users already have user_type 'staff'";
        }

        $dupEmails = DB::select(
            "SELECT email, GROUP_CONCAT(id) ids FROM staff WHERE email <> '' GROUP BY email HAVING COUNT(*) > 1"
        );
        foreach ($dupEmails as $row) {
            $problems[] = "staff #{$row->ids} share the email {$row->email}";
        }

        if ($problems) {
            throw new RuntimeException(
                "Cannot merge staff into users — fix these first (nothing was changed):\n - "
                . implode("\n - ", $problems)
            );
        }
    }

    private function uniqueUsername(object $staff, array $taken): string
    {
        foreach ([$staff->phone, 'staff_' . $staff->phone, 'staff_' . $staff->id] as $candidate) {
            if (!isset($taken[strtolower($candidate)])) {
                return $candidate;
            }
        }

        return 'staff_' . $staff->id . '_' . uniqid();
    }

    private function foreignKeysTo(string $table): array
    {
        return DB::select(
            "SELECT TABLE_NAME AS table_name, COLUMN_NAME AS column_name, CONSTRAINT_NAME AS constraint_name
             FROM information_schema.KEY_COLUMN_USAGE
             WHERE TABLE_SCHEMA = DATABASE() AND REFERENCED_TABLE_NAME = ?",
            [$table]
        );
    }

    private function isNullable(string $table, string $column): bool
    {
        return DB::selectOne(
            "SELECT IS_NULLABLE AS n FROM information_schema.COLUMNS
             WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ?",
            [$table, $column]
        )->n === 'YES';
    }
};
