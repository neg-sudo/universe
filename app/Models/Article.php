<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Article extends Model
{
    use HasFactory;

    protected $fillable = [
        'article_category_id', 'subcategory', 'subcategory_label', 'title', 'slug',
        'summary', 'content', 'difficulty', 'status', 'language', 'tags',
        'reading_time', 'word_count', 'source_name', 'source_url', 'license',
        'retrieved_at', 'published_at', 'reviewed_at',
    ];

    protected function casts(): array
    {
        return [
            'tags' => 'array',
            'reading_time' => 'integer',
            'word_count' => 'integer',
            'retrieved_at' => 'date',
            'published_at' => 'datetime',
            'reviewed_at' => 'datetime',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ArticleCategory::class, 'article_category_id');
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', 'published');
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /** Split stored plain text into display paragraphs. */
    public function paragraphs(): array
    {
        return array_values(array_filter(array_map('trim', preg_split('/\n{2,}/', $this->content) ?: [])));
    }
}
