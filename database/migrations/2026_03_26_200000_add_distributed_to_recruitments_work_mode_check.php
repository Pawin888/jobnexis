<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $driver = DB::connection()->getDriverName();

        if ($driver === 'pgsql') {
            DB::statement("ALTER TABLE recruitments DROP CONSTRAINT IF EXISTS recruitments_rc_work_mode_check");
            DB::statement("ALTER TABLE recruitments ADD CONSTRAINT recruitments_rc_work_mode_check CHECK (rc_work_mode IS NULL OR rc_work_mode IN ('onsite','remote','hybrid','distributed'))");
            return;
        }

        DB::statement("ALTER TABLE recruitments MODIFY rc_work_mode ENUM('onsite','remote','hybrid','distributed') NULL DEFAULT NULL");
    }

    public function down(): void
    {
        $driver = DB::connection()->getDriverName();

        DB::statement("UPDATE recruitments SET rc_work_mode = 'hybrid' WHERE rc_work_mode = 'distributed'");

        if ($driver === 'pgsql') {
            DB::statement("ALTER TABLE recruitments DROP CONSTRAINT IF EXISTS recruitments_rc_work_mode_check");
            DB::statement("ALTER TABLE recruitments ADD CONSTRAINT recruitments_rc_work_mode_check CHECK (rc_work_mode IS NULL OR rc_work_mode IN ('onsite','remote','hybrid'))");
            return;
        }

        DB::statement("ALTER TABLE recruitments MODIFY rc_work_mode ENUM('onsite','remote','hybrid') NULL DEFAULT NULL");
    }
};
