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
        Schema::create('media_files', function (Blueprint $table) {
            $table->bigIncrements('mf_id');
            $table->foreignId('mf_m_id')
                  ->references('m_id')->on('media')
                  ->onDelete('cascade');
            $table->string('mf_path');
            $table->string('mf_original_name');
            $table->string('mf_type')->nullable();
            $table->integer('mf_size')->default(0); // KB
            $table->timestamps();

            $table->index(['mf_m_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('media_files');
    }
};

