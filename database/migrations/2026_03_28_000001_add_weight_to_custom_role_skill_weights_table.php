<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasColumn('custom_role_skill_weights', 'weight')) {
            Schema::table('custom_role_skill_weights', function (Blueprint $table) {
                $table->unsignedTinyInteger('weight')->default(1)->after('taxonomy_level');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('custom_role_skill_weights', 'weight')) {
            Schema::table('custom_role_skill_weights', function (Blueprint $table) {
                $table->dropColumn('weight');
            });
        }
    }
};
