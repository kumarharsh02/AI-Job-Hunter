<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class JobListing extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'title',
        'company_name',
        'location',
        'work_mode',
        'employment_type',
        'source_type',
        'source_id',
        'source_url',
        'salary_min',
        'salary_max',
        'currency',
        'description',
        'requirements',
        'parsed_skills',
        'parsed_experience',
        'matching_criteria',
        'match_score',
        'status',
        'posted_at',
        'expires_at',
    ];

    protected function casts(): array
    {
        return [
            'parsed_skills' => 'array',
            'parsed_experience' => 'array',
            'matching_criteria' => 'array',
            'match_score' => 'decimal:2',
            'salary_min' => 'decimal:2',
            'salary_max' => 'decimal:2',
            'posted_at' => 'datetime',
            'expires_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function skills(): BelongsToMany
    {
        return $this->belongsToMany(Skill::class, 'skill_job_listing')
            ->withPivot(['is_required', 'proficiency_level'])
            ->withTimestamps();
    }

    public function applications(): HasMany
    {
        return $this->hasMany(Application::class);
    }

    public function skillGaps(): HasMany
    {
        return $this->hasMany(SkillGap::class);
    }

    public function referrals(): HasMany
    {
        return $this->hasMany(Referral::class);
    }

    public function scopeBySource(Builder $query, string $source): Builder
    {
        return $query->where('source_type', $source);
    }

    public function scopeNew(Builder $query): Builder
    {
        return $query->where('status', 'new');
    }

    public function scopeHighMatch(Builder $query, float $threshold = 70.00): Builder
    {
        return $query->where('match_score', '>=', $threshold);
    }

    public function getMatchBadgeColorAttribute(): string
    {
        $score = (float) $this->match_score;

        if ($score >= 80) {
            return 'green';
        }

        if ($score >= 60) {
            return 'yellow';
        }

        if ($score >= 40) {
            return 'orange';
        }

        return 'red';
    }

    public function getSourceIconAttribute(): string
    {
        return match ($this->source_type) {
            'linkedin' => 'briefcase',
            'naukri' => 'building-office',
            'indeed' => 'magnifying-glass',
            'wellfound' => 'rocket',
            default => 'globe',
        };
    }

    public function getSalaryDisplayAttribute(): string
    {
        if ($this->salary_min === null && $this->salary_max === null) {
            return 'Not disclosed';
        }

        $currency = $this->currency ?? 'INR';
        $symbol = $currency === 'USD' ? '$' : '₹';

        if ($this->salary_min !== null && $this->salary_max !== null) {
            return "{$symbol}{$this->salary_min} - {$symbol}{$this->salary_max}";
        }

        $value = $this->salary_min ?? $this->salary_max;

        return "{$symbol}{$value}";
    }
}
