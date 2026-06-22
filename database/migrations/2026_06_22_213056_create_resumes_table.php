<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('resumes', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            $table->string('title');
            $table->string('file_path');
            $table->string('file_hash', 64)->nullable();
            $table->boolean('is_active')->default(false);

            $table->json('parsed_skills')->nullable();
            $table->json('parsed_experience')->nullable();
            $table->json('parsed_education')->nullable();
            $table->json('parsed_certifications')->nullable();
            $table->json('parsed_summary')->nullable();
            $table->json('raw_parsed_data')->nullable();

            $table->unsignedInteger('years_of_experience')->nullable();
            $table->string('current_role')->nullable();

            $table->index(['user_id', 'is_active']);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('resumes');
    }
};