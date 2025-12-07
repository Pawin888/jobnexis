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
        Schema::create('certificates', function (Blueprint $table) {
            $table->bigIncrements('cer_id');
            $table->string('cer_name'); // ชื่อคอร์ส/ใบประกาศ
            $table->string('cer_image_path'); // path ของรูป
            $table->string('cer_institute_name'); // สถาบันที่ออกให้
            $table->string('cer_ref_number')->nullable(); // รหัสใบประกาศ
            $table->boolean('cer_from_lesson')->default(false); // มาจาก course หรือ import
            $table->unsignedInteger('cer_u_id')->nullable(); // ผู้ใช้
            $table->unsignedInteger('cer_c_id')->nullable(); // ถ้ามาจาก course
            $table->boolean('cer_publiced')->default(false); // เผยแพร่/ซ่อน
            $table->timestamps();

            // Foreign key เชื่อมกับ users
            $table->foreign('cer_u_id')
                  ->references('id')
                  ->on('users')
                  ->onDelete('cascade');

            // Foreign key เชื่อมกับ courses
            $table->foreign('cer_c_id')
                  ->references('c_id')
                  ->on('courses')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('certificates');
    }
};
