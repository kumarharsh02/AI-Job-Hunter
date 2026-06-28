<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('applications', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('job_listing_id')->constrained()->cascadeOnDelete();
            $table->foreignId('resume_id')->nullable()->constrained()->nullOnDelete();

            $table->string('status')->default('draft')->index();

            $table->decimal('match_score', 5, 2)->nullable();
            $table->json('match_breakdown')->nullable();

            $table->string('referral_contact')->nullable();
            $table->string('referral_source')->nullable();

            $table->timestamp('applied_at')->nullable();
            $table->timestamp('interview_scheduled_at')->nullable();
            $table->timestamp('interview_completed_at')->nullable();
            $table->timestamp('offer_received_at')->nullable();
            $table->timestamp('rejected_at')->nullable();
            $table->timestamp('withdrawn_at')->nullable();

            $table->json('interview_notes')->nullable();
            $table->json('follow_up_reminders')->nullable();

            $table->text('notes')->nullable();

            $table->unique(['user_id', 'job_listing_id']);
            $table->index(['user_id', 'status']);
            $table->index('interview_scheduled_at');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('applications');
    }
};
