<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Referral extends Model
{
    use HasFactory, SoftDeletes;

    public const STATUS_REQUESTED = 'requested';

    public const STATUS_SUBMITTED = 'submitted';

    public const STATUS_ACKNOWLEDGED = 'acknowledged';

    public const STATUS_GHOSTED = 'ghosted';

    public const STATUS_DECLINED = 'declined';

    public const STATUS_HIRED = 'hired';

    public const PLATFORM_LINKEDIN = 'linkedin';

    public const PLATFORM_COLLEAGUE = 'colleague';

    public const PLATFORM_REFERRAL = 'referral';

    public const PLATFORM_OTHER = 'other';

    protected $fillable = [
        'user_id',
        'job_listing_id',
        'application_id',
        'contact_name',
        'contact_email',
        'contact_phone',
        'platform',
        'relationship',
        'company',
        'status',
        'notes',
        'referred_at',
        'responded_at',
    ];

    protected function casts(): array
    {
        return [
            'referred_at' => 'datetime',
            'responded_at' => 'datetime',
        ];
    }

    public static function statuses(): array
    {
        return [
            self::STATUS_REQUESTED => 'Requested',
            self::STATUS_SUBMITTED => 'Submitted',
            self::STATUS_ACKNOWLEDGED => 'Acknowledged',
            self::STATUS_GHOSTED => 'Ghosted',
            self::STATUS_DECLINED => 'Declined',
            self::STATUS_HIRED => 'Hired',
        ];
    }

    public static function platforms(): array
    {
        return [
            self::PLATFORM_LINKEDIN => 'LinkedIn',
            self::PLATFORM_COLLEAGUE => 'Colleague',
            self::PLATFORM_REFERRAL => 'Referral',
            self::PLATFORM_OTHER => 'Other',
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

    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }

    public function scopeByStatus(Builder $query, string $status): Builder
    {
        return $query->where('status', $status);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->whereIn('status', [
            self::STATUS_REQUESTED,
            self::STATUS_SUBMITTED,
            self::STATUS_ACKNOWLEDGED,
        ]);
    }

    public function getStatusLabelAttribute(): string
    {
        return self::statuses()[$this->status] ?? ucfirst($this->status);
    }

    public function getPlatformLabelAttribute(): string
    {
        return self::platforms()[$this->platform] ?? ucfirst($this->platform);
    }
}
