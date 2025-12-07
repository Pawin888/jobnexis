<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('questions', function (Blueprint $table) {
            $table->bigIncrements('q_id');
            $table->string('q_question');
            $table->string('q_answer1');
            $table->string('q_answer2');
            $table->string('q_answer3');
            $table->string('q_answer4');
            $table->integer('q_correct_answer');
            $table->foreignId('q_e_id')->references('e_id')->on('exams')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('questions');
    }
};
