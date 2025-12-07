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
        Schema::create('media', function (Blueprint $table) {
            $table->bigIncrements('m_id');
            $table->string('m_name');
            $table->string('m_path')->nullable();
            $table->integer('m_index')->default(0);
            $table->text('m_desc')->nullable();

            // Optional links
            $table->foreignId('m_l_id')->nullable()
                  ->references('l_id')->on('lessons')
                  ->onDelete('cascade');
            $table->foreignId('m_c_id')->nullable()
                  ->references('c_id')->on('courses')
                  ->onDelete('cascade');

            $table->timestamps();

            // Indexes for common queries
            $table->index(['m_c_id']);
            $table->index(['m_l_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('media');
    }
};

