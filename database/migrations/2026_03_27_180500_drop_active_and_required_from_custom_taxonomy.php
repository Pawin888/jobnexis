<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('custom_job_groups', function (Blueprint $table) {
            if (Schema::hasColumn('custom_job_groups', 'is_active')) {
                $table->dropColumn('is_active');
            }
        });

        Schema::table('custom_job_roles', function (Blueprint $table) {
            if (Schema::hasColumn('custom_job_roles', 'is_active')) {
                $table->dropColumn('is_active');
            }
        });

        Schema::table('custom_skills', function (Blueprint $table) {
            if (Schema::hasColumn('custom_skills', 'is_active')) {
                $table->dropColumn('is_active');
            }
        });

        Schema::table('custom_role_skill_weights', function (Blueprint $table) {
            if (Schema::hasColumn('custom_role_skill_weights', 'is_required')) {
                $table->dropColumn('is_required');
            }
        });
    }

    public function down(): void
    {
        Schema::table('custom_job_groups', function (Blueprint $table) {
            if (!Schema::hasColumn('custom_job_groups', 'is_active')) {
                $table->boolean('is_active')->default(true);
            }
        });

        Schema::table('custom_job_roles', function (Blueprint $table) {
            if (!Schema::hasColumn('custom_job_roles', 'is_active')) {
                $table->boolean('is_active')->default(true);
            }
        });

        Schema::table('custom_skills', function (Blueprint $table) {
            if (!Schema::hasColumn('custom_skills', 'is_active')) {
                $table->boolean('is_active')->default(true);
            }
        });

        Schema::table('custom_role_skill_weights', function (Blueprint $table) {
            if (!Schema::hasColumn('custom_role_skill_weights', 'is_required')) {
                $table->boolean('is_required')->default(false);
            }
        });
    }
};
