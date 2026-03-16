<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('recruitments', function (Blueprint $table) {
            $table->string('rc_type')->nullable()->default(null)->change();
            $table->string('rc_work_mode')->nullable()->default(null)->change();
        });
    }

    public function down(): void
    {
        DB::table('recruitments')
            ->whereNull('rc_type')
            ->orWhere('rc_type', '')
            ->update(['rc_type' => 'full-time']);

        DB::table('recruitments')
            ->whereNull('rc_work_mode')
            ->orWhere('rc_work_mode', '')
            ->update(['rc_work_mode' => 'onsite']);

        if (DB::getDriverName() === 'pgsql') {
            DB::statement("UPDATE recruitments SET rc_type = split_part(rc_type, ',', 1) WHERE rc_type LIKE '%,%'");
        } else {
            DB::table('recruitments')
                ->where('rc_type', 'like', '%,%')
                ->update(['rc_type' => 'full-time']);
        }

        Schema::table('recruitments', function (Blueprint $table) {
            $table->enum('rc_type', ['full-time', 'part-time', 'intern', 'freelance'])->default('full-time')->change();
            $table->enum('rc_work_mode', ['onsite', 'remote', 'hybrid'])->default('onsite')->change();
        });
    }
};