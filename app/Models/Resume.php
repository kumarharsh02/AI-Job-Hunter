<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Resume extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'title',
        'file_path',
        'file_hash',
        'is_active',
        'parsed_skills',
        'parsed_experience',
        'parsed_education',
        'parsed_certifications',
        'parsed_summary',
        'raw_parsed_data',
        'years_of_experience',
        'current_role',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'parsed_skills' => 'array',
            'parsed_experience' => 'array',
            'parsed_education' => 'array',
            'parsed_certifications' => 'array',
            'parsed_summary' => 'array',
            'raw_parsed_data' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function applications(): HasMany
    {
        return $this->hasMany(Application::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function activate(): bool
    {
        Resume::where('user_id', $this->user_id)
            ->where('id', '!=', $this->id)
            ->update(['is_active' => false]);

        return $this->update(['is_active' => true]);
    }
}
