<?php

declare (strict_types=1);
namespace App\Models;

use App\Models\Scopes\ActiveScope;
use App\Models\Scopes\PublishedScope;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
/**
 * Post
 * 
 * Eloquent model representing the Post entity with comprehensive relationships, scopes, and business logic for the e-commerce system.
 * 
 * @property mixed $fillable
 * @method static \Illuminate\Database\Eloquent\Builder|Post newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Post newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Post query()
 * @mixin \Eloquent
 */
#[ScopedBy([ActiveScope::class, PublishedScope::class])]
final class Post extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia, LogsActivity;
    protected $fillable = ['title', 'title_translations', 'slug', 'content', 'content_translations', 'excerpt', 'excerpt_translations', 'status', 'published_at', 'user_id', 'meta_title', 'meta_title_translations', 'meta_description', 'meta_description_translations', 'featured', 'tags', 'tags_translations', 'views_count', 'likes_count', 'comments_count', 'allow_comments', 'is_pinned'];
    /**
     * Handle casts functionality with proper error handling.
     * @return array
     */
    protected function casts(): array
    {
        return ['published_at' => 'datetime', 'featured' => 'boolean', 'title_translations' => 'array', 'content_translations' => 'array', 'excerpt_translations' => 'array', 'meta_title_translations' => 'array', 'meta_description_translations' => 'array', 'tags_translations' => 'array', 'allow_comments' => 'boolean', 'is_pinned' => 'boolean'];
    }
    /**
     * Handle user functionality with proper error handling.
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
    /**
     * Handle registerMediaCollections functionality with proper error handling.
     * @return void
     */
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('images')->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/gif', 'image/webp'])->singleFile();
        $this->addMediaCollection('gallery')->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/gif', 'image/webp']);
    }
    /**
     * Handle registerMediaConversions functionality with proper error handling.
     * @param Media|null $media
     * @return void
     */
    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('thumb')->width(300)->height(200)->sharpen(10);
        $this->addMediaConversion('medium')->width(800)->height(600)->sharpen(10);
    }
    /**
     * Handle getActivitylogOptions functionality with proper error handling.
     * @return LogOptions
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logOnly(['title', 'content', 'status', 'published_at'])->logOnlyDirty()->dontSubmitEmptyLogs();
    }
    // Translation methods
    /**
     * Handle getTranslatedTitle functionality with proper error handling.
     * @param string|null $locale
     * @return string
     */
    public function getTranslatedTitle(?string $locale = null): string
    {
        $locale = $locale ?? app()->getLocale();
        $translations = $this->title_translations ?? [];
        return $translations[$locale] ?? $this->title;
    }
    /**
     * Handle getTranslatedContent functionality with proper error handling.
     * @param string|null $locale
     * @return string
     */
    public function getTranslatedContent(?string $locale = null): string
    {
        $locale = $locale ?? app()->getLocale();
        $translations = $this->content_translations ?? [];
        return $translations[$locale] ?? $this->content;
    }
    /**
     * Handle getTranslatedExcerpt functionality with proper error handling.
     * @param string|null $locale
     * @return string|null
     */
    public function getTranslatedExcerpt(?string $locale = null): ?string
    {
        $locale = $locale ?? app()->getLocale();
        $translations = $this->excerpt_translations ?? [];
        return $translations[$locale] ?? $this->excerpt;
    }
    /**
     * Handle getTranslatedMetaTitle functionality with proper error handling.
     * @param string|null $locale
     * @return string|null
     */
    public function getTranslatedMetaTitle(?string $locale = null): ?string
    {
        $locale = $locale ?? app()->getLocale();
        $translations = $this->meta_title_translations ?? [];
        return $translations[$locale] ?? $this->meta_title;
    }
    /**
     * Handle trans functionality with proper error handling.
     * @param string $field
     * @param string|null $locale
     * @return mixed
     */
    public function trans(string $field, ?string $locale = null): mixed
    {
        $locale = $locale ?? app()->getLocale();
        $translationField = $field . '_translations';
        if (property_exists($this, $translationField)) {
            $translations = $this->{$translationField} ?? [];
            return $translations[$locale] ?? $this->{$field};
        }
        return $this->{$field} ?? null;
    }
    /**
     * Handle getTranslatedMetaDescription functionality with proper error handling.
     * @param string|null $locale
     * @return string|null
     */
    public function getTranslatedMetaDescription(?string $locale = null): ?string
    {
        $locale = $locale ?? app()->getLocale();
        $translations = $this->meta_description_translations ?? [];
        return $translations[$locale] ?? $this->meta_description;
    }
    /**
     * Handle getTranslatedTags functionality with proper error handling.
     * @param string|null $locale
     * @return string|null
     */
    public function getTranslatedTags(?string $locale = null): ?string
    {
        $locale = $locale ?? app()->getLocale();
        $translations = $this->tags_translations ?? [];
        return $translations[$locale] ?? $this->tags;
    }
    // Scopes
    /**
     * Handle scopePublished functionality with proper error handling.
     * @param mixed $query
     */
    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }
    /**
     * Handle scopeFeatured functionality with proper error handling.
     * @param mixed $query
     */
    public function scopeFeatured($query)
    {
        return $query->where('featured', true);
    }
    /**
     * Handle scopePinned functionality with proper error handling.
     * @param mixed $query
     */
    public function scopePinned($query)
    {
        return $query->where('is_pinned', true);
    }
    /**
     * Handle scopeByAuthor functionality with proper error handling.
     * @param mixed $query
     * @param int $userId
     */
    public function scopeByAuthor($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }
    // Accessors
    /**
     * Handle getFormattedPublishedAtAttribute functionality with proper error handling.
     * @return string
     */
    public function getFormattedPublishedAtAttribute(): string
    {
        return $this->published_at?->format('d/m/Y H:i') ?? '';
    }
    /**
     * Handle getStatusLabelAttribute functionality with proper error handling.
     * @return string
     */
    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'draft' => __('posts.status.draft'),
            'published' => __('posts.status.published'),
            'archived' => __('posts.status.archived'),
            default => $this->status,
        };
    }
    // Enhanced Translation Methods
    /**
     * Handle getTranslatedSlug functionality with proper error handling.
     * @param string|null $locale
     * @return string
     */
    public function getTranslatedSlug(?string $locale = null): string
    {
        return $this->trans('slug', $locale) ?: $this->slug;
    }
    // Scope for translated posts
    /**
     * Handle scopeWithTranslations functionality with proper error handling.
     * @param mixed $query
     * @param string|null $locale
     */
    public function scopeWithTranslations($query, ?string $locale = null)
    {
        // Since we're using JSON-based translations, this scope doesn't need to do anything special
        // The translations are already loaded with the model
        return $query;
    }
    // Translation Management Methods
    /**
     * Handle getAvailableLocales functionality with proper error handling.
     * @return array
     */
    public function getAvailableLocales(): array
    {
        $locales = [];
        $translationFields = ['title_translations', 'content_translations', 'excerpt_translations', 'meta_title_translations', 'meta_description_translations', 'tags_translations'];
        foreach ($translationFields as $field) {
            if (isset($this->{$field}) && is_array($this->{$field})) {
                $locales = array_merge($locales, array_keys($this->{$field}));
            }
        }
        return array_unique($locales);
    }
    /**
     * Handle hasTranslationFor functionality with proper error handling.
     * @param string $locale
     * @return bool
     */
    public function hasTranslationFor(string $locale): bool
    {
        $translationFields = ['title_translations', 'content_translations', 'excerpt_translations', 'meta_title_translations', 'meta_description_translations', 'tags_translations'];
        foreach ($translationFields as $field) {
            if (isset($this->{$field}) && is_array($this->{$field}) && isset($this->{$field}[$locale])) {
                return true;
            }
        }
        return false;
    }
    /**
     * Handle updateTranslation functionality with proper error handling.
     * @param string $locale
     * @param array $data
     * @return bool
     */
    public function updateTranslation(string $locale, array $data): bool
    {
        $translationFields = ['title', 'content', 'excerpt', 'meta_title', 'meta_description', 'tags'];
        foreach ($translationFields as $field) {
            if (isset($data[$field])) {
                $translationField = $field . '_translations';
                $translations = $this->{$translationField} ?? [];
                $translations[$locale] = $data[$field];
                $this->{$translationField} = $translations;
            }
        }
        return $this->save();
    }
    /**
     * Handle getOrCreateTranslation functionality with proper error handling.
     * @param string $locale
     * @return array
     */
    public function getOrCreateTranslation(string $locale): array
    {
        $translation = [];
        $translationFields = ['title', 'content', 'excerpt', 'meta_title', 'meta_description', 'tags'];
        foreach ($translationFields as $field) {
            $translationField = $field . '_translations';
            $translations = $this->{$translationField} ?? [];
            if (!isset($translations[$locale])) {
                $translations[$locale] = $this->{$field};
                $this->{$translationField} = $translations;
            }
            $translation[$field] = $translations[$locale];
        }
        return $translation;
    }
    /**
     * Handle updateTranslations functionality with proper error handling.
     * @param array $translations
     * @return bool
     */
    public function updateTranslations(array $translations): bool
    {
        foreach ($translations as $locale => $data) {
            $this->updateTranslation($locale, $data);
        }
        return true;
    }
    // Helper Methods
    /**
     * Handle getPostInfo functionality with proper error handling.
     * @return array
     */
    public function getPostInfo(): array
    {
        return ['id' => $this->id, 'title' => $this->title, 'slug' => $this->slug, 'content' => $this->content, 'excerpt' => $this->excerpt, 'status' => $this->status, 'featured' => $this->featured, 'is_pinned' => $this->is_pinned, 'user_id' => $this->user_id, 'published_at' => $this->published_at?->toISOString(), 'views_count' => $this->views_count, 'likes_count' => $this->likes_count, 'comments_count' => $this->comments_count, 'allow_comments' => $this->allow_comments];
    }
    /**
     * Handle getSeoInfo functionality with proper error handling.
     * @return array
     */
    public function getSeoInfo(): array
    {
        return ['meta_title' => $this->meta_title, 'meta_description' => $this->meta_description, 'tags' => $this->tags, 'canonical_url' => $this->getCanonicalUrl()];
    }
    /**
     * Handle getContentInfo functionality with proper error handling.
     * @return array
     */
    public function getContentInfo(): array
    {
        return ['title' => $this->title, 'content' => $this->content, 'excerpt' => $this->excerpt, 'word_count' => $this->getWordCount(), 'reading_time' => $this->getReadingTime(), 'has_featured_image' => $this->hasFeaturedImage(), 'gallery_count' => $this->getGalleryCount()];
    }
    /**
     * Handle getStatusInfo functionality with proper error handling.
     * @return array
     */
    public function getStatusInfo(): array
    {
        return ['status' => $this->status, 'status_label' => $this->getStatusLabelAttribute(), 'status_color' => $this->getStatusColor(), 'is_published' => $this->isPublished(), 'is_draft' => $this->isDraft(), 'is_archived' => $this->isArchived(), 'featured' => $this->featured, 'is_pinned' => $this->is_pinned, 'published_at' => $this->published_at?->toISOString()];
    }
    /**
     * Handle getEngagementInfo functionality with proper error handling.
     * @return array
     */
    public function getEngagementInfo(): array
    {
        return ['views_count' => $this->views_count, 'likes_count' => $this->likes_count, 'comments_count' => $this->comments_count, 'allow_comments' => $this->allow_comments, 'engagement_rate' => $this->getEngagementRate(), 'popularity_score' => $this->getPopularityScore()];
    }
    /**
     * Handle getBusinessInfo functionality with proper error handling.
     * @return array
     */
    public function getBusinessInfo(): array
    {
        return ['author' => $this->user?->name, 'author_id' => $this->user_id, 'created_at' => $this->created_at?->toISOString(), 'updated_at' => $this->updated_at?->toISOString(), 'days_since_created' => $this->getDaysSinceCreated(), 'days_since_published' => $this->getDaysSincePublished()];
    }
    /**
     * Handle getCompleteInfo functionality with proper error handling.
     * @param string|null $locale
     * @return array
     */
    public function getCompleteInfo(?string $locale = null): array
    {
        return array_merge($this->getPostInfo(), $this->getSeoInfo(), $this->getContentInfo(), $this->getStatusInfo(), $this->getEngagementInfo(), $this->getBusinessInfo(), ['translations' => $this->getAvailableLocales(), 'has_translations' => count($this->getAvailableLocales()) > 0, 'media_count' => $this->getMediaCount()]);
    }
    // Additional helper methods
    /**
     * Handle getStatusColor functionality with proper error handling.
     * @return string
     */
    public function getStatusColor(): string
    {
        return match ($this->status) {
            'published' => 'success',
            'draft' => 'warning',
            'archived' => 'danger',
            default => 'gray',
        };
    }
    /**
     * Handle isPublished functionality with proper error handling.
     * @return bool
     */
    public function isPublished(): bool
    {
        return $this->status === 'published';
    }
    /**
     * Handle isDraft functionality with proper error handling.
     * @return bool
     */
    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }
    /**
     * Handle isArchived functionality with proper error handling.
     * @return bool
     */
    public function isArchived(): bool
    {
        return $this->status === 'archived';
    }
    /**
     * Handle getCanonicalUrl functionality with proper error handling.
     * @return string
     */
    public function getCanonicalUrl(): string
    {
        return route('posts.show', $this);
    }
    /**
     * Handle getWordCount functionality with proper error handling.
     * @return int
     */
    public function getWordCount(): int
    {
        return str_word_count(strip_tags($this->content));
    }
    /**
     * Handle getReadingTime functionality with proper error handling.
     * @return int
     */
    public function getReadingTime(): int
    {
        $wordCount = $this->getWordCount();
        $wordsPerMinute = 200;
        // Average reading speed
        return max(1, round($wordCount / $wordsPerMinute));
    }
    /**
     * Handle hasFeaturedImage functionality with proper error handling.
     * @return bool
     */
    public function hasFeaturedImage(): bool
    {
        return $this->hasMedia('images');
    }
    /**
     * Handle getGalleryCount functionality with proper error handling.
     * @return int
     */
    public function getGalleryCount(): int
    {
        return $this->getMedia('gallery')->count();
    }
    /**
     * Handle getMediaCount functionality with proper error handling.
     * @return int
     */
    public function getMediaCount(): int
    {
        return $this->getMedia()->count();
    }
    /**
     * Handle getEngagementRate functionality with proper error handling.
     * @return float
     */
    public function getEngagementRate(): float
    {
        if ($this->views_count === 0) {
            return 0;
        }
        $totalEngagement = $this->likes_count + $this->comments_count;
        return round($totalEngagement / $this->views_count * 100, 2);
    }
    /**
     * Handle getPopularityScore functionality with proper error handling.
     * @return int
     */
    public function getPopularityScore(): int
    {
        $views = $this->views_count ?? 0;
        $likes = $this->likes_count ?? 0;
        $comments = $this->comments_count ?? 0;
        // Weighted scoring: views (1x), likes (2x), comments (3x)
        return $views * 1 + $likes * 2 + $comments * 3;
    }
    /**
     * Handle getDaysSinceCreated functionality with proper error handling.
     * @return int
     */
    public function getDaysSinceCreated(): int
    {
        return (int) $this->created_at->diffInDays(now());
    }
    /**
     * Handle getDaysSincePublished functionality with proper error handling.
     * @return int|null
     */
    public function getDaysSincePublished(): ?int
    {
        return $this->published_at ? (int) $this->published_at->diffInDays(now()) : null;
    }
    /**
     * Handle getFullDisplayName functionality with proper error handling.
     * @param string|null $locale
     * @return string
     */
    public function getFullDisplayName(?string $locale = null): string
    {
        $title = $this->getTranslatedTitle($locale);
        $status = $this->getStatusLabelAttribute();
        return "{$title} ({$status})";
    }
    /**
     * Handle scopeRecent functionality with proper error handling.
     * @param mixed $query
     * @param int $days
     */
    public function scopeRecent($query, int $days = 30)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }
    /**
     * Handle scopePopular functionality with proper error handling.
     * @param mixed $query
     * @param int $minViews
     */
    public function scopePopular($query, int $minViews = 100)
    {
        return $query->where('views_count', '>=', $minViews);
    }
    /**
     * Handle scopeWithHighEngagement functionality with proper error handling.
     * @param mixed $query
     * @param float $minRate
     */
    public function scopeWithHighEngagement($query, float $minRate = 5.0)
    {
        return $query->where('views_count', '>', 0)->whereRaw('(likes_count + comments_count) * 100 >= views_count * ?', [$minRate]);
    }
    /**
     * Handle scopeAllowComments functionality with proper error handling.
     * @param mixed $query
     */
    public function scopeAllowComments($query)
    {
        return $query->where('allow_comments', true);
    }
    /**
     * Handle scopeWithMedia functionality with proper error handling.
     * @param mixed $query
     */
    public function scopeWithMedia($query)
    {
        return $query->has('media');
    }
    /**
     * Handle scopeWithoutMedia functionality with proper error handling.
     * @param mixed $query
     */
    public function scopeWithoutMedia($query)
    {
        return $query->doesntHave('media');
    }
}