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

    public const STATUS_DRAFT = 'draft';

    public const STATUS_APPLIED = 'applied';

    public const STATUS_CONTACTED = 'contacted';

    public const STATUS_INTERVIEW_SCHEDULED = 'interview_scheduled';

    public const STATUS_INTERVIEW_COMPLETED = 'interview_completed';

    public const STATUS_OFFER_RECEIVED = 'offer_received';

    public const STATUS_REJECTED = 'rejected';

    public const STATUS_WITHDRAWN = 'withdrawn';

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

    public static function statuses(): array
    {
        return [
            self::STATUS_DRAFT => 'Draft',
            self::STATUS_APPLIED => 'Applied',
            self::STATUS_CONTACTED => 'Contacted',
            self::STATUS_INTERVIEW_SCHEDULED => 'Interviewing',
            self::STATUS_INTERVIEW_COMPLETED => 'Interview Done',
            self::STATUS_OFFER_RECEIVED => 'Offer',
            self::STATUS_REJECTED => 'Rejected',
            self::STATUS_WITHDRAWN => 'Withdrawn',
        ];
    }

    public static function statusColors(): array
    {
        return [
            self::STATUS_DRAFT => 'gray',
            self::STATUS_APPLIED => 'blue',
            self::STATUS_CONTACTED => 'cyan',
            self::STATUS_INTERVIEW_SCHEDULED => 'yellow',
            self::STATUS_INTERVIEW_COMPLETED => 'indigo',
            self::STATUS_OFFER_RECEIVED => 'green',
            self::STATUS_REJECTED => 'red',
            self::STATUS_WITHDRAWN => 'gray',
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

    public function interviews(): HasMany
    {
        return $this->hasMany(Interview::class);
    }

    public function referrals(): HasMany
    {
        return $this->hasMany(Referral::class);
    }

    public function scopeByStatus(Builder $query, string $status): Builder
    {
        return $query->where('status', $status);
    }

    public function scopeWithUpcomingInterviews(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_INTERVIEW_SCHEDULED)
            ->where('interview_scheduled_at', '>=', now());
    }

    public function scopeNeedingFollowUp(Builder $query): Builder
    {
        return $query->whereNotNull('follow_up_reminders')
            ->where('status', self::STATUS_APPLIED);
    }

    public function scopeStaleApplied(Builder $query, int $days = 7): Builder
    {
        return $query->where('status', self::STATUS_APPLIED)
            ->where('applied_at', '<=', now()->subDays($days));
    }

    public function getStatusLabelAttribute(): string
    {
        return self::statuses()[$this->status] ?? ucfirst(str_replace('_', ' ', $this->status));
    }

    public function getStatusColorAttribute(): string
    {
        return self::statusColors()[$this->status] ?? 'gray';
    }
}
