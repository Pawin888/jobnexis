<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('job_applications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('recruitment_id')->constrained('recruitments', 'rc_id')->onDelete('cascade');
            $table->foreignId('jobber_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('resume_id')->constrained('resumes')->onDelete('cascade');
            $table->enum('status', ['applied', 'reviewing', 'accepted', 'rejected', 'withdrawn'])->default('applied');
            $table->text('cover_letter')->nullable();
            $table->timestamp('applied_at')->useCurrent();
            $table->timestamp('reviewed_at')->nullable();
            $table->text('review_note')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('recruitment_id');
            $table->index('jobber_id');
            $table->index('status');
            $table->unique(['recruitment_id', 'jobber_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('job_applications');
    }
};