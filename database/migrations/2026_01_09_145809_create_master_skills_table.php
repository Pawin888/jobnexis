<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('master_skills', function (Blueprint $table) {
            $table->id();

            // ESCO identifiers
            $table->string('esco_uri')->unique();
            $table->string('esco_code')->nullable()->unique();

            // Data from skills_en.csv
            $table->string('name'); // preferredLabel
            $table->text('description')->nullable(); // description / definition

            // Optional meta
            $table->string('level')->default('intermediate');
            $table->string('source')->default('ESCO');
            $table->boolean('is_active')->default(true);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('master_skills');
    }
};
