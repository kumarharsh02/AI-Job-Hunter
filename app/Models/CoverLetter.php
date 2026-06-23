<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class CoverLetter extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'application_id',
        'tone',
        'content',
        'ai_metadata',
        'model_used',
        'tokens_used',
        'version',
    ];

    protected function casts(): array
    {
        return [
            'ai_metadata' => 'array',
            'version' => 'integer',
            'tokens_used' => 'integer',
        ];
    }

    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }

    public function scopeLatestVersion(Builder $query): Builder
    {
        return $query->orderByDesc('version');
    }
}
