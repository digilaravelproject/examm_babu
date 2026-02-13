<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('skills', function (Blueprint $table) {
            // 1. Add micro_category_id column (nullable first for data migration)
            $table->unsignedBigInteger('micro_category_id')->nullable()->after('section_id');
            $table->foreign('micro_category_id')
                ->references('id')
                ->on('micro_categories')
                ->onDelete('cascade');
        });

        // 2. Data Migration: Assign a default MicroCategory to existing skills
        // You may need to customize this query based on your data structure
        $firstMicroCategory = DB::table('micro_categories')->where('is_active', true)->first();

        if ($firstMicroCategory) {
            DB::table('skills')
                ->whereNull('micro_category_id')
                ->update(['micro_category_id' => $firstMicroCategory->id]);
        }

        // 3. Make micro_category_id NOT NULL after data migration
        Schema::table('skills', function (Blueprint $table) {
            $table->unsignedBigInteger('micro_category_id')->nullable(false)->change();
        });

        // 4. Drop section_id column and its foreign key
        Schema::table('skills', function (Blueprint $table) {
            $table->dropForeign(['section_id']);
            $table->dropColumn('section_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('skills', function (Blueprint $table) {
            // Re-add section_id
            $table->unsignedBigInteger('section_id')->nullable()->after('id');
            $table->foreign('section_id')
                ->references('id')
                ->on('sections')
                ->onDelete('cascade');

            // Drop micro_category_id
            $table->dropForeign(['micro_category_id']);
            $table->dropColumn('micro_category_id');
        });
    }
};
