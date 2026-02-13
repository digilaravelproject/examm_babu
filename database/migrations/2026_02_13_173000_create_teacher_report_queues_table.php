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
        Schema::create('teacher_report_queues', function (Blueprint $table) {
            $table->id();
            $table->string('teacher_email')->index();
            $table->string('student_name');
            $table->string('student_email')->nullable();
            $table->string('exam_name');
            $table->unsignedBigInteger('user_id'); // Link to student
            $table->unsignedBigInteger('exam_session_id'); // Link to session
            $table->string('score')->nullable();
            $table->double('total_marks', 8, 2)->default(0);
            $table->text('result_url'); // Signed URL
            $table->enum('status', ['pending', 'sent', 'failed'])->default('pending');
            $table->timestamps();

            // Foreign keys (optional but recommended)
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('exam_session_id')->references('id')->on('exam_sessions')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('teacher_report_queues');
    }
};
