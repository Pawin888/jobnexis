<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('provider_saved_candidates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('provider_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('resume_id')->constrained('resumes')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['provider_id', 'resume_id']);
            $table->index('provider_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('provider_saved_candidates');
    }
};
