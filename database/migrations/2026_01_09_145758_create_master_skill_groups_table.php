<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('master_skill_groups', function (Blueprint $table) {
            $table->id();

            // ESCO identifiers
            $table->string('esco_uri')->unique();
            $table->string('esco_code')->nullable()->unique();

            // Data from skillGroups_en.csv
            $table->string('name'); // preferredLabel
            $table->text('description')->nullable(); // description

            // SkillGroup → SkillGroup (hierarchy)
            $table->foreignId('parent_id')
                  ->nullable()
                  ->constrained('master_skill_groups')
                  ->nullOnDelete();

            // Meta
            $table->string('source')->default('ESCO');
            $table->boolean('is_active')->default(true);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('master_skill_groups');
    }
};
