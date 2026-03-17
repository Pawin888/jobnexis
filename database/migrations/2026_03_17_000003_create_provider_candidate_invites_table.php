<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('provider_candidate_invites', function (Blueprint $table) {
            $table->id();
            $table->foreignId('provider_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('resume_id')->constrained('resumes')->cascadeOnDelete();
            $table->foreignId('recruitment_id')->constrained('recruitments', 'rc_id')->cascadeOnDelete();
            $table->text('message')->nullable();
            $table->timestamp('invited_at')->useCurrent();
            $table->timestamps();

            $table->unique(['provider_id', 'resume_id', 'recruitment_id']);
            $table->index('provider_id');
            $table->index('recruitment_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('provider_candidate_invites');
    }
};
