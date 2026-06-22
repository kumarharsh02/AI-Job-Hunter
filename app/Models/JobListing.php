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
}