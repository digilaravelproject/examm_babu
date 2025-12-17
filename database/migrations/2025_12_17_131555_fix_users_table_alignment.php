<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        /**
         * 0️⃣ SAFETY: ensure no NULL user_name before NOT NULL
         */
        DB::statement("
            UPDATE users
            SET user_name = CONCAT('user_', id)
            WHERE user_name IS NULL OR user_name = ''
        ");

        /**
         * 1️⃣ DROP EXTRA COLUMN (name) – Breeze leftover
         */
        if (Schema::hasColumn('users', 'name')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('name');
            });
        }

        /**
         * 2️⃣ FIX user_name → NOT NULL (keep UNIQUE)
         * DBAL avoid using raw SQL
         */
        DB::statement("
            ALTER TABLE users
            MODIFY user_name VARCHAR(255) NOT NULL
        ");

        /**
         * 3️⃣ FIX preferences collation to match OLD DB
         * utf8mb4_bin
         */
        if (Schema::hasColumn('users', 'preferences')) {
            DB::statement("
                ALTER TABLE users
                MODIFY preferences LONGTEXT
                CHARACTER SET utf8mb4
                COLLATE utf8mb4_bin
                NULL
            ");
        }

        /**
         * 4️⃣ ADD UNIQUE INDEX ON mobile (ONLY if not exists)
         */
        $indexes = DB::select("SHOW INDEX FROM users WHERE Key_name = 'users_mobile_unique'");
        if (empty($indexes)) {
            Schema::table('users', function (Blueprint $table) {
                $table->unique('mobile', 'users_mobile_unique');
            });
        }
    }

    public function down(): void
    {
        /**
         * Minimal rollback (safe for production)
         */

        // remove mobile unique
        $indexes = DB::select("SHOW INDEX FROM users WHERE Key_name = 'users_mobile_unique'");
        if (!empty($indexes)) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropUnique('users_mobile_unique');
            });
        }

        // add name back (optional)
        if (!Schema::hasColumn('users', 'name')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('name')->after('user_name');
            });
        }

        // user_name back to nullable
        DB::statement("
            ALTER TABLE users
            MODIFY user_name VARCHAR(255) NULL
        ");

        // preferences collation rollback (optional)
        DB::statement("
            ALTER TABLE users
            MODIFY preferences LONGTEXT
            CHARACTER SET utf8mb4
            COLLATE utf8mb4_unicode_ci
            NULL
        ");
    }
};
