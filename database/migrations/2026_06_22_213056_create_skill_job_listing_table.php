<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('skill_job_listing', function (Blueprint $table) {
            $table->id();

            $table->foreignId('skill_id')->constrained()->cascadeOnDelete();
            $table->foreignId('job_listing_id')->constrained()->cascadeOnDelete();

            $table->boolean('is_required')->default(true);
            $table->string('proficiency_level')->nullable();

            $table->unique(['skill_id', 'job_listing_id']);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('skill_job_listing');
    }
};