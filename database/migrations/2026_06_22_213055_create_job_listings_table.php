<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('job_listings', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            $table->string('title');
            $table->string('company_name');
            $table->string('location')->nullable();
            $table->string('work_mode')->default('onsite');
            $table->string('employment_type')->default('full-time');

            $table->string('source_type')->index();
            $table->string('source_id')->nullable();
            $table->string('source_url')->nullable();

            $table->decimal('salary_min', 12, 2)->nullable();
            $table->decimal('salary_max', 12, 2)->nullable();
            $table->string('currency', 3)->default('INR');

            $table->longText('description')->nullable();
            $table->longText('requirements')->nullable();

            $table->json('parsed_skills')->nullable();
            $table->json('parsed_experience')->nullable();
            $table->json('matching_criteria')->nullable();

            $table->decimal('match_score', 5, 2)->nullable();
            $table->string('status')->default('new')->index();

            $table->timestamp('posted_at')->nullable();
            $table->timestamp('expires_at')->nullable();

            $table->unique(['source_type', 'source_id']);
            $table->index(['user_id', 'status']);
            $table->index(['user_id', 'match_score']);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('job_listings');
    }
};
