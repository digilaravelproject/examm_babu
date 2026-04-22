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
        Schema::create('ai_import_batches', function (Blueprint $table) {
            $table->string('id')->primary(); // Using the batchId as primary key
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('topic_id')->constrained()->onDelete('cascade');
            $table->string('status')->default('pending'); // pending, processing, completed, failed
            $table->string('pdf_path');
            $table->integer('start_page')->default(1);
            $table->integer('end_page')->default(999);
            $table->integer('questions_count')->default(0);
            $table->integer('progress')->default(0);
            $table->string('message')->nullable();
            $table->text('error_details')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ai_import_batches');
    }
};
