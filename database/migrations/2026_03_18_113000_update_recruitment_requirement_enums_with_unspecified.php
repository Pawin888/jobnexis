<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $driver = DB::connection()->getDriverName();

        if ($driver === 'pgsql') {
            DB::statement("ALTER TABLE recruitments DROP CONSTRAINT IF EXISTS recruitments_rc_gender_check");
            DB::statement("ALTER TABLE recruitments DROP CONSTRAINT IF EXISTS recruitments_rc_education_level_check");
            DB::statement("ALTER TABLE recruitments DROP CONSTRAINT IF EXISTS recruitments_rc_experience_level_check");

            DB::statement("ALTER TABLE recruitments ALTER COLUMN rc_gender DROP NOT NULL");
            DB::statement("ALTER TABLE recruitments ALTER COLUMN rc_education_level DROP NOT NULL");
            DB::statement("ALTER TABLE recruitments ALTER COLUMN rc_experience_level DROP NOT NULL");

            DB::statement("ALTER TABLE recruitments ALTER COLUMN rc_gender SET DEFAULT 'unspecified'");
            DB::statement("ALTER TABLE recruitments ALTER COLUMN rc_education_level SET DEFAULT 'unspecified'");
            DB::statement("ALTER TABLE recruitments ALTER COLUMN rc_experience_level SET DEFAULT 'unspecified'");

            DB::statement("ALTER TABLE recruitments ADD CONSTRAINT recruitments_rc_gender_check CHECK (rc_gender IN ('unspecified','any','male','female'))");
            DB::statement("ALTER TABLE recruitments ADD CONSTRAINT recruitments_rc_education_level_check CHECK (rc_education_level IN ('unspecified','any','below_bachelor','bachelor','master','doctorate'))");
            DB::statement("ALTER TABLE recruitments ADD CONSTRAINT recruitments_rc_experience_level_check CHECK (rc_experience_level IN ('unspecified','no_experience','0_1','1_3','3_5','more_5'))");

            return;
        }

        DB::statement("ALTER TABLE recruitments MODIFY rc_gender ENUM('unspecified','any','male','female') NULL DEFAULT 'unspecified'");
        DB::statement("ALTER TABLE recruitments MODIFY rc_education_level ENUM('unspecified','any','below_bachelor','bachelor','master','doctorate') NULL DEFAULT 'unspecified'");
        DB::statement("ALTER TABLE recruitments MODIFY rc_experience_level ENUM('unspecified','no_experience','0_1','1_3','3_5','more_5') NULL DEFAULT 'unspecified'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $driver = DB::connection()->getDriverName();

        DB::statement("UPDATE recruitments SET rc_gender = 'any' WHERE rc_gender IS NULL OR rc_gender = 'unspecified'");
        DB::statement("UPDATE recruitments SET rc_education_level = 'any' WHERE rc_education_level IS NULL OR rc_education_level = 'unspecified'");
        DB::statement("UPDATE recruitments SET rc_education_level = 'master' WHERE rc_education_level = 'doctorate'");
        DB::statement("UPDATE recruitments SET rc_experience_level = 'no_experience' WHERE rc_experience_level IS NULL OR rc_experience_level = 'unspecified'");

        if ($driver === 'pgsql') {
            DB::statement("ALTER TABLE recruitments DROP CONSTRAINT IF EXISTS recruitments_rc_gender_check");
            DB::statement("ALTER TABLE recruitments DROP CONSTRAINT IF EXISTS recruitments_rc_education_level_check");
            DB::statement("ALTER TABLE recruitments DROP CONSTRAINT IF EXISTS recruitments_rc_experience_level_check");

            DB::statement("ALTER TABLE recruitments ALTER COLUMN rc_gender SET DEFAULT 'any'");
            DB::statement("ALTER TABLE recruitments ALTER COLUMN rc_education_level SET DEFAULT 'any'");
            DB::statement("ALTER TABLE recruitments ALTER COLUMN rc_experience_level SET DEFAULT 'no_experience'");

            DB::statement("ALTER TABLE recruitments ALTER COLUMN rc_gender SET NOT NULL");
            DB::statement("ALTER TABLE recruitments ALTER COLUMN rc_education_level SET NOT NULL");
            DB::statement("ALTER TABLE recruitments ALTER COLUMN rc_experience_level SET NOT NULL");

            DB::statement("ALTER TABLE recruitments ADD CONSTRAINT recruitments_rc_gender_check CHECK (rc_gender IN ('any','male','female'))");
            DB::statement("ALTER TABLE recruitments ADD CONSTRAINT recruitments_rc_education_level_check CHECK (rc_education_level IN ('any','below_bachelor','bachelor','master'))");
            DB::statement("ALTER TABLE recruitments ADD CONSTRAINT recruitments_rc_experience_level_check CHECK (rc_experience_level IN ('no_experience','0_1','1_3','3_5','more_5'))");

            return;
        }

        DB::statement("ALTER TABLE recruitments MODIFY rc_gender ENUM('any','male','female') NOT NULL DEFAULT 'any'");
        DB::statement("ALTER TABLE recruitments MODIFY rc_education_level ENUM('any','below_bachelor','bachelor','master') NOT NULL DEFAULT 'any'");
        DB::statement("ALTER TABLE recruitments MODIFY rc_experience_level ENUM('no_experience','0_1','1_3','3_5','more_5') NOT NULL DEFAULT 'no_experience'");
    }
};
