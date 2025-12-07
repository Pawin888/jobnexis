<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('exam_skill', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('exam_id');
            $table->unsignedBigInteger('skill_id');
            $table->timestamps();

            $table->foreign('exam_id')->references('e_id')->on('exams')->onDelete('cascade');
            $table->foreign('skill_id')->references('id')->on('skills')->onDelete('cascade');
            $table->unique(['exam_id', 'skill_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exam_skill');
    }
};

