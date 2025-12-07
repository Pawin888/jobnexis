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
        Schema::create('user_profiles', function (Blueprint $table) {
            $table->id('up_id');
            $table->string('up_prefix')->nullable();
            $table->string('up_name')->nullable();
            $table->string('up_city')->nullable();
            $table->date('up_birth_date')->nullable();
            $table->string('up_gender')->nullable();
            $table->string('up_phone')->nullable();
            $table->foreignId('up_u_id')->constrained('users')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_profiles');
    }
};
