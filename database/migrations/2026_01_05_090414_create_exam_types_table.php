<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateExamTypesTable extends Migration
{
    public function up()
    {
        Schema::create('exam_types', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name');
            $table->boolean('status')->default(1); // 1 = Active, 0 = Inactive
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('exam_types');
    }
}
