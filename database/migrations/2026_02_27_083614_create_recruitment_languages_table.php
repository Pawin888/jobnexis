<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('recruitment_languages', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('rc_id');
            $table->string('language');
            $table->enum('proficiency', ['basic', 'conversational', 'fluent', 'native']);
            $table->timestamps();

            $table->foreign('rc_id')->references('rc_id')->on('recruitments')->onDelete('cascade');
            $table->index('rc_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('recruitment_languages');
    }
};