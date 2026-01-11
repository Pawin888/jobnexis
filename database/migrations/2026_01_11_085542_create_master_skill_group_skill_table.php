<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('master_skill_group_skill', function (Blueprint $table) {
            $table->id();

            $table->foreignId('master_skill_group_id')
                  ->constrained('master_skill_groups')
                  ->cascadeOnDelete();

            $table->foreignId('master_skill_id')
                  ->constrained('master_skills')
                  ->cascadeOnDelete();

            // ป้องกันข้อมูลซ้ำ (skill เดิม อยู่ group เดิม ซ้ำไม่ได้)
            $table->unique(
                ['master_skill_group_id', 'master_skill_id'],
                'uniq_skill_group_skill'
            );

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('master_skill_group_skill');
    }
};
