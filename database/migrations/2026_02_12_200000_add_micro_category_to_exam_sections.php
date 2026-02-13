<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('exam_sections', function (Blueprint $table) {
            // Add micro_category_id, nullable initially
            if (!Schema::hasColumn('exam_sections', 'micro_category_id')) {
                $table->unsignedBigInteger('micro_category_id')->nullable()->after('section_id');
                // You might want to add a foreign key constraint here if needed
                // $table->foreign('micro_category_id')->references('id')->on('micro_categories')->onDelete('set null');
            }

            // Make section_id nullable if it's not already
            $table->unsignedBigInteger('section_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('exam_sections', function (Blueprint $table) {
            $table->dropColumn('micro_category_id');
            // Reverting section_id to not null might need data cleanup, so be careful
             $table->unsignedBigInteger('section_id')->nullable(false)->change();
        });
    }
};
