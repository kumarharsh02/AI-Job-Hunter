<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Application extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'job_listing_id',
        'resume_id',
        'status',
        'match_score',
        'match_breakdown',
        'referral_contact',
        'referral_source',
        'applied_at',
        'interview_scheduled_at',
        'interview_completed_at',
        'offer_received_at',
        'rejected_at',
        'withdrawn_at',
        'interview_notes',
        'follow_up_reminders',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'match_score' => 'decimal:2',
            'match_breakdown' => 'array',
            'interview_notes' => 'array',
            'follow_up_reminders' => 'array',
            'applied_at' => 'datetime',
            'interview_scheduled_at' => 'datetime',
            'interview_completed_at' => 'datetime',
            'offer_received_at' => 'datetime',
            'rejected_at' => 'datetime',
            'withdrawn_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function jobListing(): BelongsTo
    {
        return $this->belongsTo(JobListing::class);
    }

    public function resume(): BelongsTo
    {
        return $this->belongsTo(Resume::class);
    }

    public function coverLetters(): HasMany
    {
        return $this->hasMany(CoverLetter::class);
    }

    public function scopeByStatus(Builder $query, string $status): Builder
    {
        return $query->where('status', $status);
    }

    public function scopeWithUpcomingInterviews(Builder $query): Builder
    {
        return $query->where('status', 'interview_scheduled')
            ->where('interview_scheduled_at', '>=', now());
    }

    public function scopeNeedingFollowUp(Builder $query): Builder
    {
        return $query->whereNotNull('follow_up_reminders')
            ->where('status', 'applied');
    }
}