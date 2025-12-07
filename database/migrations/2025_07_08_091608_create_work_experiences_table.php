<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('work_experiences', function (Blueprint $table) {
            $table->bigIncrements('we_id');
            $table->string('we_company_name');
            $table->date('we_start_date');
            $table->date('we_end_date')->nullable();
            $table->foreignId('we_u_id')->constrained('users')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('work_experiences');
    }
};
