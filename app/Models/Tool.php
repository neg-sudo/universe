<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Tool extends Model
{
    use HasFactory;

    protected $fillable = [
        'tool_category_id', 'name', 'slug', 'description', 'icon', 'keywords',
        'inputs', 'version', 'status', 'featured', 'usage_count',
    ];

    protected function casts(): array
    {
        return [
            'keywords' => 'array',
            'inputs' => 'array',
            'featured' => 'boolean',
            'usage_count' => 'integer',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ToolCategory::class, 'tool_category_id');
    }

    public function usageEvents(): HasMany
    {
        return $this->hasMany(ToolUsageEvent::class);
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', 'published');
    }

    public function scopeFeatured(Builder $query): Builder
    {
        return $query->where('featured', true);
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
