<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Interview extends Model
{
    use HasFactory, SoftDeletes;

    public const TYPE_HR = 'hr';

    public const TYPE_TECHNICAL = 'technical';

    public const TYPE_FOUNDER = 'founder';

    public const TYPE_MANAGEMENT = 'management';

    public const TYPE_CULTURE_FIT = 'culture_fit';

    public const STATUS_SCHEDULED = 'scheduled';

    public const STATUS_COMPLETED = 'completed';

    public const STATUS_CANCELLED = 'cancelled';

    public const STATUS_RESCHEDULED = 'rescheduled';

    protected $fillable = [
        'application_id',
        'user_id',
        'interview_type',
        'title',
        'notes',
        'location',
        'meeting_link',
        'scheduled_at',
        'completed_at',
        'cancelled_at',
        'status',
        'interviewer_details',
    ];

    protected function casts(): array
    {
        return [
            'scheduled_at' => 'datetime',
            'completed_at' => 'datetime',
            'cancelled_at' => 'datetime',
            'interviewer_details' => 'array',
        ];
    }

    public static function types(): array
    {
        return [
            self::TYPE_HR => 'HR Round',
            self::TYPE_TECHNICAL => 'Technical',
            self::TYPE_FOUNDER => 'Founder',
            self::TYPE_MANAGEMENT => 'Management',
            self::TYPE_CULTURE_FIT => 'Culture Fit',
        ];
    }

    public static function statuses(): array
    {
        return [
            self::STATUS_SCHEDULED => 'Scheduled',
            self::STATUS_COMPLETED => 'Completed',
            self::STATUS_CANCELLED => 'Cancelled',
            self::STATUS_RESCHEDULED => 'Rescheduled',
        ];
    }

    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeUpcoming(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_SCHEDULED)
            ->where('scheduled_at', '>=', now())
            ->orderBy('scheduled_at');
    }

    public function scopeWithin24Hours(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_SCHEDULED)
            ->whereBetween('scheduled_at', [now(), now()->addDay()]);
    }

    public function getTypeLabelAttribute(): string
    {
        return self::types()[$this->interview_type] ?? ucfirst($this->interview_type);
    }

    public function getStatusLabelAttribute(): string
    {
        return self::statuses()[$this->status] ?? ucfirst($this->status);
    }

    public function markAsCompleted(): bool
    {
        return $this->update([
            'status' => self::STATUS_COMPLETED,
            'completed_at' => now(),
        ]);
    }

    public function markAsCancelled(): bool
    {
        return $this->update([
            'status' => self::STATUS_CANCELLED,
            'cancelled_at' => now(),
        ]);
    }
}
