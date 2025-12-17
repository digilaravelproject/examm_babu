<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {

            // Names
            $table->string('first_name')->after('id');
            $table->string('last_name')->nullable()->after('first_name');

            // username nullable (NO UNIQUE YET)
            $table->string('user_name')->nullable()->after('last_name');

            // Mobile
            $table->string('mobile')->nullable()->after('email');
            $table->timestamp('mobile_verified_at')->nullable()->after('mobile');

            // Verification
            $table->string('verification_code')->nullable()->after('mobile_verified_at');
            $table->timestamp('verification_code_expires_at')->nullable()->after('verification_code');

            // Preferences
            $table->longText('preferences')->nullable()->after('remember_token');

            // 2FA
            $table->text('two_factor_secret')->nullable()->after('password');
            $table->text('two_factor_recovery_codes')->nullable()->after('two_factor_secret');

            // Profile / Status
            $table->unsignedBigInteger('current_team_id')->nullable()->after('remember_token');
            $table->text('profile_photo_path')->nullable()->after('current_team_id');
            $table->boolean('is_active')->default(1)->after('profile_photo_path');

            // Soft delete
            $table->softDeletes();
        });

        /**
         * 🔥 AUTO FIX EXISTING DATA
         * user_name = user_{id}
         */
        DB::statement("
            UPDATE users
            SET user_name = CONCAT('user_', id)
            WHERE user_name IS NULL OR user_name = ''
        ");

        /**
         * 🔥 ADD UNIQUE INDEX SAFELY (NO DBAL)
         */
        Schema::table('users', function (Blueprint $table) {
            $table->unique('user_name', 'users_user_name_unique');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {

            $table->dropUnique('users_user_name_unique');

            $table->dropColumn([
                'first_name',
                'last_name',
                'user_name',
                'mobile',
                'mobile_verified_at',
                'verification_code',
                'verification_code_expires_at',
                'preferences',
                'two_factor_secret',
                'two_factor_recovery_codes',
                'current_team_id',
                'profile_photo_path',
                'is_active',
                'deleted_at',
            ]);
        });
    }
};
