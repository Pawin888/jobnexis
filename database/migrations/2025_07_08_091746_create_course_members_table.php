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
        Schema::create('course_members', function (Blueprint $table) {
            $table->bigIncrements('cm_id');
            $table->foreignId('cm_c_id')->references('c_id')->on('courses')->onDelete('cascade');
            $table->foreignId('cm_u_id')->constrained('users')->onDelete('cascade');
            $table->boolean('cm_passed')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('course_members');
    }
};
