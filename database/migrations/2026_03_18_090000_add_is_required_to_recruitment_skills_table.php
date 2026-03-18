<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('recruitment_skills', function (Blueprint $table) {
            if (!Schema::hasColumn('recruitment_skills', 'is_required')) {
                $table->boolean('is_required')->default(true)->after('proficiency_level');
            }
        });
    }

    public function down(): void
    {
        Schema::table('recruitment_skills', function (Blueprint $table) {
            if (Schema::hasColumn('recruitment_skills', 'is_required')) {
                $table->dropColumn('is_required');
            }
        });
    }
};