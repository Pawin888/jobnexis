<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('custom_role_skill_weights', 'taxonomy_level')) {
            Schema::table('custom_role_skill_weights', function (Blueprint $table) {
                $table->string('taxonomy_level', 32)->default('remembering')->after('custom_skill_id');
            });
        }

        if (Schema::hasColumn('custom_role_skill_weights', 'min_level')) {
            DB::statement("\n                UPDATE custom_role_skill_weights\n                SET taxonomy_level = CASE min_level\n                    WHEN 'beginner' THEN 'remembering'\n                    WHEN 'intermediate' THEN 'understanding'\n                    WHEN 'advanced' THEN 'analyzing'\n                    WHEN 'expert' THEN 'creating'\n                    ELSE 'remembering'\n                END\n            ");
        }

        if (Schema::hasColumn('custom_role_skill_weights', 'weight') || Schema::hasColumn('custom_role_skill_weights', 'min_level')) {
            Schema::table('custom_role_skill_weights', function (Blueprint $table) {
                if (Schema::hasColumn('custom_role_skill_weights', 'weight')) {
                    $table->dropColumn('weight');
                }
                if (Schema::hasColumn('custom_role_skill_weights', 'min_level')) {
                    $table->dropColumn('min_level');
                }
            });
        }
    }

    public function down(): void
    {
        Schema::table('custom_role_skill_weights', function (Blueprint $table) {
            $table->decimal('weight', 4, 2)->default(0.50)->after('custom_skill_id');
            $table->string('min_level', 32)->default('beginner')->after('weight');
        });

        DB::statement("\n            UPDATE custom_role_skill_weights\n            SET min_level = CASE taxonomy_level\n                WHEN 'remembering' THEN 'beginner'\n                WHEN 'understanding' THEN 'intermediate'\n                WHEN 'applying' THEN 'intermediate'\n                WHEN 'analyzing' THEN 'advanced'\n                WHEN 'evaluating' THEN 'expert'\n                WHEN 'creating' THEN 'expert'\n                ELSE 'beginner'\n            END\n        ");

        Schema::table('custom_role_skill_weights', function (Blueprint $table) {
            $table->dropColumn('taxonomy_level');
        });
    }
};
