<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
{
Schema::create('education_profiles', function (Blueprint $table) {
$table->id('e_id');
$table->string('e_name');
$table->string('e_phone')->nullable();
$table->string('e_email');
$table->string('e_website')->nullable();
$table->date('e_birthday')->nullable();
$table->string('e_number')->nullable();
$table->string('e_address')->nullable();
$table->string('e_province')->nullable();
$table->text('e_detail')->nullable();
$table->foreignId('e_u_id')->constrained('users')->onDelete('cascade');
$table->timestamps();
});
}


public function down(): void
{
Schema::dropIfExists('education_profiles');
}
};
