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
        Schema::create('lessons', function (Blueprint $table) {
            $table->bigIncrements('l_id');
            $table->string('l_name');
            $table->string('l_description')->nullable();
            $table->enum('l_status', ['open', 'closed', 'draft'])->default('draft');
            $table->string('l_index');
            $table->foreignId('l_c_id')->references('c_id')->on('courses')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lessons');
    }
};
