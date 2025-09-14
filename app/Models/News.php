<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\HasTranslations;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

final /**
 * News
 * 
 * Eloquent model representing a database entity with relationships and business logic.
 */
class News extends Model
{
    use HasFactory;
    use HasTranslations;

    protected $table = 'news';

    protected $fillable = [
        'is_visible',
        'is_featured',
        'published_at',
        'author_name',
        'author_email',
        'view_count',
        'meta_data',
    ];

    protected function casts(): array
    {
        return [
            'is_visible' => 'boolean',
            'is_featured' => 'boolean',
            'published_at' => 'datetime',
            'view_count' => 'integer',
            'meta_data' => 'array',
        ];
    }

    protected string $translationModel = \App\Models\Translations\NewsTranslation::class;

    public function isPublished(): bool
    {
        return (bool) $this->is_visible && (bool) $this->published_at && $this->published_at <= now();
    }

    public function isFeatured(): bool
    {
        return (bool) $this->is_featured;
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(NewsCategory::class, 'news_category_pivot', 'news_id', 'news_category_id')
            ->withTimestamps();
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(NewsTag::class, 'news_tag_pivot', 'news_id', 'news_tag_id')
            ->withTimestamps();
    }

    public function comments(): HasMany
    {
        return $this->hasMany(NewsComment::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(NewsImage::class);
    }

    public function incrementViewCount(): void
    {
        $this->increment('view_count');
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('is_visible', true)
            ->where('published_at', '<=', now());
    }

    public function scopeFeatured(Builder $query): Builder
    {
        return $query->where('is_featured', true);
    }

    public function scopeByCategory(Builder $query, int $categoryId): Builder
    {
        return $query->whereHas('categories', function (Builder $q) use ($categoryId) {
            $q->where('news_category_id', $categoryId);
        });
    }

    public function scopeByTag(Builder $query, int $tagId): Builder
    {
        return $query->whereHas('tags', function (Builder $q) use ($tagId) {
            $q->where('news_tag_id', $tagId);
        });
    }

    public function scopeSearch(Builder $query, string $search): Builder
    {
        return $query->whereHas('translations', function (Builder $q) use ($search) {
            $q->where('title', 'like', "%{$search}%")
                ->orWhere('summary', 'like', "%{$search}%")
                ->orWhere('content', 'like', "%{$search}%");
        });
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function getSlugAttribute(): string
    {
        return $this->getTranslation('slug', app()->getLocale());
    }

    public function getTitleAttribute(): string
    {
        return $this->getTranslation('title', app()->getLocale());
    }

    public function getSummaryAttribute(): ?string
    {
        return $this->getTranslation('summary', app()->getLocale());
    }

    public function getContentAttribute(): ?string
    {
        return $this->getTranslation('content', app()->getLocale());
    }

    public function getSeoTitleAttribute(): ?string
    {
        return $this->getTranslation('seo_title', app()->getLocale());
    }

    public function getSeoDescriptionAttribute(): ?string
    {
        return $this->getTranslation('seo_description', app()->getLocale());
    }
}
