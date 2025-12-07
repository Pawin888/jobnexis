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
        Schema::create('exams', function (Blueprint $table) {
            $table->bigIncrements('e_id');
            $table->string('e_name');
            $table->text('e_description')->nullable();
            $table->foreignId('e_l_id')->nullable()->references('l_id')->on('lessons')->onDelete('cascade'); // เพิ่ม nullable()
            $table->foreignId('e_c_id')->references('c_id')->on('courses')->onDelete('cascade'); // เพิ่ม FK คอร์ส
            $table->integer('e_index')->default(0); // เพิ่มลำดับ
            $table->json('e_skills')->nullable()->after('e_description');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('exams');
    }
};
