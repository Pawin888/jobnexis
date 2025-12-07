<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('course_members', function (Blueprint $table) {
            $table->unique(['cm_c_id', 'cm_u_id'], 'course_members_unique_course_user');
        });
    }

    public function down(): void
    {
        Schema::table('course_members', function (Blueprint $table) {
            $table->dropUnique('course_members_unique_course_user');
        });
    }
};

