<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('micro_categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sub_category_id')->constrained('sub_categories')->onDelete('cascade');
            $table->string('name');
            $table->string('code')->unique(); // System generated unique code
            $table->string('image_path')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('micro_categories');
    }
};
