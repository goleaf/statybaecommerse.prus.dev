<?php

declare (strict_types=1);
namespace App\Models;

use App\Models\Scopes\ActiveScope;
use App\Models\Scopes\PublishedScope;
use App\Models\Scopes\VisibleScope;
use App\Traits\HasTranslations;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
/**
 * News
 * 
 * Eloquent model representing the News entity with comprehensive relationships, scopes, and business logic for the e-commerce system.
 * 
 * @property mixed $table
 * @property mixed $fillable
 * @property string $translationModel
 * @method static \Illuminate\Database\Eloquent\Builder|News newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|News newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|News query()
 * @mixin \Eloquent
 */
#[ScopedBy([ActiveScope::class, PublishedScope::class, VisibleScope::class])]
final class News extends Model
{
    use HasFactory;
    use HasTranslations;
    protected $table = 'news';
    protected $fillable = ['is_visible', 'is_featured', 'published_at', 'author_name', 'author_email', 'view_count', 'meta_data'];
    /**
     * Handle casts functionality with proper error handling.
     * @return array
     */
    protected function casts(): array
    {
        return ['is_visible' => 'boolean', 'is_featured' => 'boolean', 'published_at' => 'datetime', 'view_count' => 'integer', 'meta_data' => 'array'];
    }
    protected string $translationModel = \App\Models\Translations\NewsTranslation::class;
    /**
     * Handle isPublished functionality with proper error handling.
     * @return bool
     */
    public function isPublished(): bool
    {
        return (bool) $this->is_visible && (bool) $this->published_at && $this->published_at <= now();
    }
    /**
     * Handle isFeatured functionality with proper error handling.
     * @return bool
     */
    public function isFeatured(): bool
    {
        return (bool) $this->is_featured;
    }
    /**
     * Handle categories functionality with proper error handling.
     * @return BelongsToMany
     */
    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(NewsCategory::class, 'news_category_pivot', 'news_id', 'news_category_id')->withTimestamps();
    }
    /**
     * Handle tags functionality with proper error handling.
     * @return BelongsToMany
     */
    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(NewsTag::class, 'news_tag_pivot', 'news_id', 'news_tag_id')->withTimestamps();
    }
    /**
     * Handle comments functionality with proper error handling.
     * @return HasMany
     */
    public function comments(): HasMany
    {
        return $this->hasMany(NewsComment::class);
    }
    /**
     * Handle latestComment functionality with proper error handling.
     * @return HasOne
     */
    public function latestComment(): HasOne
    {
        return $this->comments()->one()->latestOfMany();
    }
    /**
     * Handle images functionality with proper error handling.
     * @return HasMany
     */
    public function images(): HasMany
    {
        return $this->hasMany(NewsImage::class);
    }
    /**
     * Handle latestImage functionality with proper error handling.
     * @return HasOne
     */
    public function latestImage(): HasOne
    {
        return $this->images()->one()->latestOfMany();
    }
    /**
     * Handle incrementViewCount functionality with proper error handling.
     * @return void
     */
    public function incrementViewCount(): void
    {
        $this->increment('view_count');
    }
    /**
     * Handle scopePublished functionality with proper error handling.
     * @param Builder $query
     * @return Builder
     */
    public function scopePublished(Builder $query): Builder
    {
        return $query->where('is_visible', true)->where('published_at', '<=', now());
    }
    /**
     * Handle scopeFeatured functionality with proper error handling.
     * @param Builder $query
     * @return Builder
     */
    public function scopeFeatured(Builder $query): Builder
    {
        return $query->where('is_featured', true);
    }
    /**
     * Handle scopeByCategory functionality with proper error handling.
     * @param Builder $query
     * @param int $categoryId
     * @return Builder
     */
    public function scopeByCategory(Builder $query, int $categoryId): Builder
    {
        return $query->whereHas('categories', function (Builder $q) use ($categoryId) {
            $q->where('news_category_id', $categoryId);
        });
    }
    /**
     * Handle scopeByTag functionality with proper error handling.
     * @param Builder $query
     * @param int $tagId
     * @return Builder
     */
    public function scopeByTag(Builder $query, int $tagId): Builder
    {
        return $query->whereHas('tags', function (Builder $q) use ($tagId) {
            $q->where('news_tag_id', $tagId);
        });
    }
    /**
     * Handle scopeSearch functionality with proper error handling.
     * @param Builder $query
     * @param string $search
     * @return Builder
     */
    public function scopeSearch(Builder $query, string $search): Builder
    {
        return $query->whereHas('translations', function (Builder $q) use ($search) {
            $q->where('title', 'like', "%{$search}%")->orWhere('summary', 'like', "%{$search}%")->orWhere('content', 'like', "%{$search}%");
        });
    }
    /**
     * Handle getRouteKeyName functionality with proper error handling.
     * @return string
     */
    public function getRouteKeyName(): string
    {
        return 'slug';
    }
    /**
     * Handle getSlugAttribute functionality with proper error handling.
     * @return string
     */
    public function getSlugAttribute(): string
    {
        return $this->getTranslation('slug', app()->getLocale());
    }
    /**
     * Handle getTitleAttribute functionality with proper error handling.
     * @return string
     */
    public function getTitleAttribute(): string
    {
        return $this->getTranslation('title', app()->getLocale());
    }
    /**
     * Handle getSummaryAttribute functionality with proper error handling.
     * @return string|null
     */
    public function getSummaryAttribute(): ?string
    {
        return $this->getTranslation('summary', app()->getLocale());
    }
    /**
     * Handle getContentAttribute functionality with proper error handling.
     * @return string|null
     */
    public function getContentAttribute(): ?string
    {
        return $this->getTranslation('content', app()->getLocale());
    }
    /**
     * Handle getSeoTitleAttribute functionality with proper error handling.
     * @return string|null
     */
    public function getSeoTitleAttribute(): ?string
    {
        return $this->getTranslation('seo_title', app()->getLocale());
    }
    /**
     * Handle getSeoDescriptionAttribute functionality with proper error handling.
     * @return string|null
     */
    public function getSeoDescriptionAttribute(): ?string
    {
        return $this->getTranslation('seo_description', app()->getLocale());
    }
}