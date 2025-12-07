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
        Schema::create('recruitments', function (Blueprint $table) {
            $table->bigIncrements('rc_id');

            $table->string('rc_title');
            $table->text('rc_description');
            $table->text('rc_requirements')->nullable();

            // เก็บข้อความอิสระเช่น "30,000" หรือ "ตามตกลง" ไว้ก่อน
            $table->string('rc_salary')->nullable();

            // สถานที่: ข้อความ + ลิงก์ยาว เผื่อ Google Maps
            $table->string('rc_location_text')->nullable();
            $table->string('rc_location_link', 2048)->nullable();

            $table->enum('rc_type', ['full-time', 'part-time', 'intern', 'freelance'])->default('full-time');
            $table->enum('rc_status', ['open', 'closed', 'draft'])->default('open');

            // โหมดการทำงาน ใช้บ่อยมากในตลาดงานปัจจุบัน
            $table->enum('rc_work_mode', ['onsite', 'remote', 'hybrid'])->default('onsite');

            // ใช้ timestamp จะยืดหยุ่นกว่า (มีเวลา) และตั้ง default เป็นเวลาปัจจุบัน
            $table->timestamp('rc_posted_at')->useCurrent();
            $table->date('rc_expire_at')->nullable();

            // ใครเป็นคนโพสต์ (ผู้ให้บริการ/บริษัท)
            $table->foreignId('rc_u_id')->constrained('users')->onDelete('cascade');

            // เผื่อสถิติเล็กน้อยและช่องทางสมัคร
            $table->unsignedBigInteger('rc_views')->default(0);
            $table->string('rc_application_url', 2048)->nullable();

            $table->timestamps();
            $table->softDeletes();

            // Index ทั่วไป
            $table->index('rc_u_id');
            $table->index('rc_status');
            $table->index(['rc_status', 'rc_posted_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recruitments');
    }
};
