<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('skill_gaps', function (Blueprint $table) {
            $table->id();

            $table->foreignId('job_listing_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            $table->string('skill_name');
            $table->string('category')->default('technical');
            $table->text('context')->nullable();

            $table->boolean('is_addressed')->default(false);

            $table->unique(['job_listing_id', 'skill_name']);
            $table->index(['user_id', 'is_addressed']);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('skill_gaps');
    }
};
