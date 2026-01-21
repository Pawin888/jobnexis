<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('resume_skills', function (Blueprint $table) {
            $table->id();
            $table->foreignId('resume_id')->constrained()->cascadeOnDelete();

            $table->foreignId('skill_group_id')
                ->constrained('master_skill_groups')
                ->cascadeOnDelete();

            $table->foreignId('skill_id')
                ->constrained('master_skills')
                ->cascadeOnDelete();

            $table->enum('proficiency_level', [
                'beginner',
                'intermediate',
                'advanced',
                'expert'
            ]);

            $table->timestamps();

            // ป้องกันเลือก skill ซ้ำ
            $table->unique(['resume_id', 'skill_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('resume_skills');
    }
};