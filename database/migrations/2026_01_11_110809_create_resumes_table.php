<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('resumes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            $table->string('first_name');
            $table->string('middle_name')->nullable();
            $table->string('last_name');
            $table->date('birth_date');

            $table->enum('gender', ['male', 'female', 'other'])->nullable();
            
            $table->string('email');
            $table->string('phone');

            $table->text('summary')->nullable();

            $table->date('available_start_date')->nullable();
            $table->integer('expected_salary')->nullable();
            $table->string('preferred_location')->nullable();

            $table->boolean('is_visible')->default(true);

            $table->string('profile_image')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('resumes');
    }
};
