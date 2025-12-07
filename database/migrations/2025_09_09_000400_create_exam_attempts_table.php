<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('exam_attempts', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('exam_id');
            $table->unsignedInteger('score')->default(0);
            $table->unsignedInteger('total_questions')->default(0);
            $table->unsignedInteger('required')->nullable();
            $table->boolean('passed')->default(false);
            $table->decimal('percentage', 5, 2)->default(0);
            $table->json('answers')->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('exam_id')->references('e_id')->on('exams')->onDelete('cascade');
            $table->index(['exam_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exam_attempts');
    }
};

