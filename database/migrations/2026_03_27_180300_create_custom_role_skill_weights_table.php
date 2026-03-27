<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('custom_role_skill_weights', function (Blueprint $table) {
            $table->id();
            $table->foreignId('custom_job_role_id')->constrained('custom_job_roles')->cascadeOnDelete();
            $table->foreignId('custom_skill_id')->constrained('custom_skills')->cascadeOnDelete();
            $table->string('taxonomy_level', 32)->default('remembering');
            $table->timestamps();

            $table->unique(['custom_job_role_id', 'custom_skill_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('custom_role_skill_weights');
    }
};
