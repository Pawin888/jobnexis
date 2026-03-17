<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('job_applications', function (Blueprint $table) {
            $table->boolean('is_shortlisted')->default(false)->after('status');
            $table->timestamp('shortlisted_at')->nullable()->after('is_shortlisted');
            $table->text('internal_note')->nullable()->after('review_note');

            $table->index('is_shortlisted');
            $table->index('shortlisted_at');
        });
    }

    public function down(): void
    {
        Schema::table('job_applications', function (Blueprint $table) {
            $table->dropIndex(['is_shortlisted']);
            $table->dropIndex(['shortlisted_at']);
            $table->dropColumn(['is_shortlisted', 'shortlisted_at', 'internal_note']);
        });
    }
};
