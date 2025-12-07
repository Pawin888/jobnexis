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
        Schema::create('companies_profiles', function (Blueprint $table) {
            $table->bigIncrements('co_id');
            $table->string('co_name');
            $table->string('co_email')->nullable();
            $table->string('co_phone')->nullable();
            $table->date('co_birthday')->nullable();
            $table->string('co_type')->nullable();
            $table->string('co_number')->nullable();
            $table->integer('co_jobber_amount')->nullable();
            $table->string('co_address')->nullable();
            $table->string('co_province')->nullable();
            $table->text('co_details')->nullable();
            $table->string('co_profile_img')->nullable();
            $table->string('co_banner_img')->nullable();
            $table->unsignedBigInteger('co_user_id')->nullable();
            $table->timestamps();

            // ความสัมพันธ์กับ users
            $table->foreign('co_user_id')
                  ->references('id')
                  ->on('users')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('companies_profiles');
    }
};
