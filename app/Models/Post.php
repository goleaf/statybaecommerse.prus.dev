<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

final class Post extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia, LogsActivity;

    protected $fillable = [
        'title',
        'title_translations',
        'slug',
        'content',
        'content_translations',
        'excerpt',
        'excerpt_translations',
        'status',
        'published_at',
        'user_id',
        'meta_title',
        'meta_title_translations',
        'meta_description',
        'meta_description_translations',
        'featured',
        'tags',
        'tags_translations',
        'views_count',
        'likes_count',
        'comments_count',
        'allow_comments',
        'is_pinned',
    ];


    protected function casts(): array
    {
        return [
            'published_at' => 'datetime',
            'featured' => 'boolean',
            'title_translations' => 'array',
            'content_translations' => 'array',
            'excerpt_translations' => 'array',
            'meta_title_translations' => 'array',
            'meta_description_translations' => 'array',
            'tags_translations' => 'array',
            'allow_comments' => 'boolean',
            'is_pinned' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function registerMediaCollections(): void
    {
        $this
            ->addMediaCollection('images')
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/gif', 'image/webp'])
            ->singleFile();

        $this
            ->addMediaCollection('gallery')
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/gif', 'image/webp']);
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        $this
            ->addMediaConversion('thumb')
            ->width(300)
            ->height(200)
            ->sharpen(10);

        $this
            ->addMediaConversion('medium')
            ->width(800)
            ->height(600)
            ->sharpen(10);
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['title', 'content', 'status', 'published_at'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    // Translation methods
    public function getTranslatedTitle(?string $locale = null): string
    {
        $locale = $locale ?? app()->getLocale();
        $translations = $this->title_translations ?? [];

        return $translations[$locale] ?? $this->title;
    }

    public function getTranslatedContent(?string $locale = null): string
    {
        $locale = $locale ?? app()->getLocale();
        $translations = $this->content_translations ?? [];

        return $translations[$locale] ?? $this->content;
    }

    public function getTranslatedExcerpt(?string $locale = null): ?string
    {
        $locale = $locale ?? app()->getLocale();
        $translations = $this->excerpt_translations ?? [];

        return $translations[$locale] ?? $this->excerpt;
    }

    public function getTranslatedMetaTitle(?string $locale = null): ?string
    {
        $locale = $locale ?? app()->getLocale();
        $translations = $this->meta_title_translations ?? [];

        return $translations[$locale] ?? $this->meta_title;
    }

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

    public function getTranslatedMetaDescription(?string $locale = null): ?string
    {
        $locale = $locale ?? app()->getLocale();
        $translations = $this->meta_description_translations ?? [];

        return $translations[$locale] ?? $this->meta_description;
    }

    public function getTranslatedTags(?string $locale = null): ?string
    {
        $locale = $locale ?? app()->getLocale();
        $translations = $this->tags_translations ?? [];

        return $translations[$locale] ?? $this->tags;
    }

    // Scopes
    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }

    public function scopeFeatured($query)
    {
        return $query->where('featured', true);
    }

    public function scopePinned($query)
    {
        return $query->where('is_pinned', true);
    }

    public function scopeByAuthor($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    // Accessors
    public function getFormattedPublishedAtAttribute(): string
    {
        return $this->published_at?->format('d/m/Y H:i') ?? '';
    }

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
    public function getTranslatedSlug(?string $locale = null): string
    {
        return $this->trans('slug', $locale) ?: $this->slug;
    }

    // Scope for translated posts
    public function scopeWithTranslations($query, ?string $locale = null)
    {
        // Since we're using JSON-based translations, this scope doesn't need to do anything special
        // The translations are already loaded with the model
        return $query;
    }

    // Translation Management Methods
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


    public function updateTranslations(array $translations): bool
    {
        foreach ($translations as $locale => $data) {
            $this->updateTranslation($locale, $data);
        }
        return true;
    }

    // Helper Methods
    public function getPostInfo(): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'content' => $this->content,
            'excerpt' => $this->excerpt,
            'status' => $this->status,
            'featured' => $this->featured,
            'is_pinned' => $this->is_pinned,
            'user_id' => $this->user_id,
            'published_at' => $this->published_at?->toISOString(),
            'views_count' => $this->views_count,
            'likes_count' => $this->likes_count,
            'comments_count' => $this->comments_count,
            'allow_comments' => $this->allow_comments,
        ];
    }

    public function getSeoInfo(): array
    {
        return [
            'meta_title' => $this->meta_title,
            'meta_description' => $this->meta_description,
            'tags' => $this->tags,
            'canonical_url' => $this->getCanonicalUrl(),
        ];
    }

    public function getContentInfo(): array
    {
        return [
            'title' => $this->title,
            'content' => $this->content,
            'excerpt' => $this->excerpt,
            'word_count' => $this->getWordCount(),
            'reading_time' => $this->getReadingTime(),
            'has_featured_image' => $this->hasFeaturedImage(),
            'gallery_count' => $this->getGalleryCount(),
        ];
    }

    public function getStatusInfo(): array
    {
        return [
            'status' => $this->status,
            'status_label' => $this->getStatusLabelAttribute(),
            'status_color' => $this->getStatusColor(),
            'is_published' => $this->isPublished(),
            'is_draft' => $this->isDraft(),
            'is_archived' => $this->isArchived(),
            'featured' => $this->featured,
            'is_pinned' => $this->is_pinned,
            'published_at' => $this->published_at?->toISOString(),
        ];
    }

    public function getEngagementInfo(): array
    {
        return [
            'views_count' => $this->views_count,
            'likes_count' => $this->likes_count,
            'comments_count' => $this->comments_count,
            'allow_comments' => $this->allow_comments,
            'engagement_rate' => $this->getEngagementRate(),
            'popularity_score' => $this->getPopularityScore(),
        ];
    }

    public function getBusinessInfo(): array
    {
        return [
            'author' => $this->user?->name,
            'author_id' => $this->user_id,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
            'days_since_created' => $this->getDaysSinceCreated(),
            'days_since_published' => $this->getDaysSincePublished(),
        ];
    }

    public function getCompleteInfo(?string $locale = null): array
    {
        return array_merge(
            $this->getPostInfo(),
            $this->getSeoInfo(),
            $this->getContentInfo(),
            $this->getStatusInfo(),
            $this->getEngagementInfo(),
            $this->getBusinessInfo(),
            [
                'translations' => $this->getAvailableLocales(),
                'has_translations' => count($this->getAvailableLocales()) > 0,
                'media_count' => $this->getMediaCount(),
            ]
        );
    }

    // Additional helper methods
    public function getStatusColor(): string
    {
        return match ($this->status) {
            'published' => 'success',
            'draft' => 'warning',
            'archived' => 'danger',
            default => 'gray',
        };
    }

    public function isPublished(): bool
    {
        return $this->status === 'published';
    }

    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }

    public function isArchived(): bool
    {
        return $this->status === 'archived';
    }

    public function getCanonicalUrl(): string
    {
        return route('posts.show', $this);
    }

    public function getWordCount(): int
    {
        return str_word_count(strip_tags($this->content));
    }

    public function getReadingTime(): int
    {
        $wordCount = $this->getWordCount();
        $wordsPerMinute = 200; // Average reading speed
        return max(1, round($wordCount / $wordsPerMinute));
    }

    public function hasFeaturedImage(): bool
    {
        return $this->hasMedia('images');
    }

    public function getGalleryCount(): int
    {
        return $this->getMedia('gallery')->count();
    }

    public function getMediaCount(): int
    {
        return $this->getMedia()->count();
    }

    public function getEngagementRate(): float
    {
        if ($this->views_count === 0) {
            return 0;
        }
        
        $totalEngagement = $this->likes_count + $this->comments_count;
        return round(($totalEngagement / $this->views_count) * 100, 2);
    }

    public function getPopularityScore(): int
    {
        $views = $this->views_count ?? 0;
        $likes = $this->likes_count ?? 0;
        $comments = $this->comments_count ?? 0;
        
        // Weighted scoring: views (1x), likes (2x), comments (3x)
        return ($views * 1) + ($likes * 2) + ($comments * 3);
    }

    public function getDaysSinceCreated(): int
    {
        return (int) $this->created_at->diffInDays(now());
    }

    public function getDaysSincePublished(): ?int
    {
        return $this->published_at ? (int) $this->published_at->diffInDays(now()) : null;
    }

    public function getFullDisplayName(?string $locale = null): string
    {
        $title = $this->getTranslatedTitle($locale);
        $status = $this->getStatusLabelAttribute();
        return "{$title} ({$status})";
    }

    public function scopeRecent($query, int $days = 30)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    public function scopePopular($query, int $minViews = 100)
    {
        return $query->where('views_count', '>=', $minViews);
    }

    public function scopeWithHighEngagement($query, float $minRate = 5.0)
    {
        return $query->where('views_count', '>', 0)
                    ->whereRaw('(likes_count + comments_count) * 100 >= views_count * ?', [$minRate]);
    }

    public function scopeAllowComments($query)
    {
        return $query->where('allow_comments', true);
    }

    public function scopeWithMedia($query)
    {
        return $query->has('media');
    }

    public function scopeWithoutMedia($query)
    {
        return $query->doesntHave('media');
    }
}
