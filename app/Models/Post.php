<?php declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

final class Post extends Model implements HasMedia
{
    use HasFactory, LogsActivity, InteractsWithMedia;

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

    protected $casts = [
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

    public function registerMediaConversions(Media $media = null): void
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
}
