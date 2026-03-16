<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('recruitments', function (Blueprint $table) {
            $table->enum('rc_gender', ['any', 'male', 'female'])->default('any')->after('rc_requirements');
            $table->unsignedTinyInteger('rc_age_min')->nullable()->after('rc_gender');
            $table->unsignedTinyInteger('rc_age_max')->nullable()->after('rc_age_min');
            $table->enum('rc_education_level', ['any', 'below_bachelor', 'bachelor', 'master'])->default('any')->after('rc_age_max');
            $table->enum('rc_experience_level', ['no_experience', '0_1', '1_3', '3_5', 'more_5'])->default('no_experience')->after('rc_education_level');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('recruitments', function (Blueprint $table) {
            $table->dropColumn([
                'rc_gender',
                'rc_age_min',
                'rc_age_max',
                'rc_education_level',
                'rc_experience_level',
            ]);
        });
    }
};
