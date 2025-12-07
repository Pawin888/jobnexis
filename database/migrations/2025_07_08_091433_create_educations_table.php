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
        Schema::create('educations', function (Blueprint $table) {
            $table->bigIncrements('ed_id');
            $table->string('ed_name');
            $table->date('ed_start_date')->nullable(false);
            $table->date('ed_end_date')->nullable(false);
            $table->foreignId('ed_u_id')->constrained('users')->onDelete('cascade');
            $table->timestamps();
        });
    }
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('educations');
    }
};
