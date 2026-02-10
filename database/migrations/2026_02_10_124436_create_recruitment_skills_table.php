<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('recruitment_skills', function (Blueprint $table) {
            $table->id();

            // งานที่รับสมัคร
            $table->unsignedBigInteger('rc_id');
            $table->foreign('rc_id')
                  ->references('rc_id')
                  ->on('recruitments')
                  ->cascadeOnDelete();

            // กลุ่มสกิล
            $table->foreignId('master_skill_group_id')
                  ->constrained('master_skill_groups')
                  ->cascadeOnDelete();

            // สกิล
            $table->foreignId('master_skill_id')
                  ->constrained('master_skills')
                  ->cascadeOnDelete();

            // ระดับความชำนาญ
            $table->enum(
                'proficiency_level',
                ['beginner', 'intermediate', 'advanced', 'expert']
            );

            $table->timestamps();

            // ป้องกันสกิลซ้ำในงานเดียวกัน
            $table->unique(
                ['rc_id', 'master_skill_id'],
                'uniq_recruitment_skill'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('recruitment_skills');
    }
};
