<?php

declare(strict_types=1);

namespace App\Models;

use App\Contracts\TranslatableRecord;
use App\Enums\ModerationState;
use App\Models\Scopes\ActiveScope;
use App\Models\Scopes\PublishedScope;
use App\Models\Scopes\VisibleScope;
use App\Services\Security\HtmlContentSanitizer;
use App\Traits\HasTranslations;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * News
 *
 * Eloquent model representing the News entity with comprehensive relationships, scopes, and business logic for the e-commerce system.
 *
 * @property mixed  $table
 * @property mixed  $fillable
 * @property string $translationModel
 *
 * @method static \Illuminate\Database\Eloquent\Builder|News newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|News newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|News query()
 *
 * @mixin \Eloquent
 */
#[ScopedBy([ActiveScope::class, PublishedScope::class, VisibleScope::class])]
final class News extends Model implements TranslatableRecord
{
    use HasFactory;
    use HasTranslations;
    use LogsActivity;
    use SoftDeletes;

    protected $table = 'news';

    protected $fillable = [
        'is_visible',
        'is_featured',
        'is_breaking',
        'moderation_state',
        'submitted_for_review_at',
        'approved_at',
        'approved_by_id',
        'published_at',
        'author_name',
        'author_email',
        'view_count',
        'meta_data',
    ];

    /**
     * Handle casts functionality with proper error handling.
     */
    protected function casts(): array
    {
        return [
            'is_visible'              => 'boolean',
            'is_featured'             => 'boolean',
            'is_breaking'             => 'boolean',
            'moderation_state'        => ModerationState::class,
            'submitted_for_review_at' => 'datetime',
            'approved_at'             => 'datetime',
            'approved_by_id'          => 'integer',
            'published_at'            => 'datetime',
            'view_count'              => 'integer',
            'meta_data'               => 'array',
        ];
    }

    protected string $translationModel = \App\Models\Translations\NewsTranslation::class;

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by_id');
    }

    public function approvals(): HasMany
    {
        return $this->hasMany(NewsApproval::class);
    }

    public function latestApproval(): HasOne
    {
        return $this->approvals()->one()->latestOfMany('decided_at');
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'moderation_state',
                'submitted_for_review_at',
                'approved_at',
                'approved_by_id',
                'is_visible',
                'is_featured',
                'is_breaking',
                'published_at',
                'author_name',
                'author_email',
            ])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    /**
     * Handle isPublished functionality with proper error handling.
     */
    public function isPublished(): bool
    {
        return $this->moderation_state === ModerationState::Published
            && (bool) $this->is_visible
            && (bool) $this->published_at
            && $this->published_at <= now();
    }

    public function getIsPublishedAttribute(): bool
    {
        return $this->isPublished();
    }

    /**
     * Handle isFeatured functionality with proper error handling.
     */
    public function isFeatured(): bool
    {
        return (bool) $this->is_featured;
    }

    /**
     * Handle categories functionality with proper error handling.
     */
    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(NewsCategory::class, 'news_category_pivot', 'news_id', 'news_category_id');
    }

    /**
     * Handle tags functionality with proper error handling.
     */
    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(NewsTag::class, 'news_tag_pivot', 'news_id', 'news_tag_id');
    }

    /**
     * Handle comments functionality with proper error handling.
     */
    public function comments(): HasMany
    {
        return $this->hasMany(NewsComment::class);
    }

    /**
     * Handle latestComment functionality with proper error handling.
     */
    public function latestComment(): HasOne
    {
        return $this->comments()->one()->latestOfMany();
    }

    /**
     * Handle images functionality with proper error handling.
     */
    public function images(): HasMany
    {
        return $this->hasMany(NewsImage::class);
    }

    /**
     * Handle latestImage functionality with proper error handling.
     */
    public function latestImage(): HasOne
    {
        return $this->images()->one()->latestOfMany();
    }

    /**
     * Handle incrementViewCount functionality with proper error handling.
     */
    public function incrementViewCount(): void
    {
        $this->increment('view_count');
    }

    /**
     * Handle scopePublished functionality with proper error handling.
     */
    public function scopePublished(Builder $query): Builder
    {
        return $query
            ->where('moderation_state', ModerationState::Published->value)
            ->where('is_visible', true)
            ->where('published_at', '<=', now());
    }

    /**
     * Handle scopeFeatured functionality with proper error handling.
     */
    public function scopeFeatured(Builder $query): Builder
    {
        return $query->where('is_featured', true);
    }

    /**
     * Provide a deterministic alphabetical ordering using the author name field when available.
     */
    public function scopeOrderedByName(Builder $query): Builder
    {
        // Order null author names last for consistent pagination while keeping creation order as a tiebreaker.
        return $query
            ->orderByRaw('author_name IS NULL')
            ->orderBy('author_name')
            ->orderBy('id');
    }

    /**
     * Handle scopeByCategory functionality with proper error handling.
     */
    public function scopeByCategory(Builder $query, int $categoryId): Builder
    {
        return $query->whereHas('categories', function (Builder $q) use ($categoryId): void {
            $q->where('news_category_id', $categoryId);
        });
    }

    /**
     * Handle scopeByTag functionality with proper error handling.
     */
    public function scopeByTag(Builder $query, int $tagId): Builder
    {
        return $query->whereHas('tags', function (Builder $q) use ($tagId): void {
            $q->where('news_tag_id', $tagId);
        });
    }

    /**
     * Handle scopeSearch functionality with proper error handling.
     */
    public function scopeSearch(Builder $query, string $search): Builder
    {
        return $query->whereHas('translations', function (Builder $q) use ($search): void {
            $q->where('title', 'like', "%{$search}%")->orWhere('summary', 'like', "%{$search}%")->orWhere('content', 'like', "%{$search}%");
        });
    }

    /**
     * Handle getRouteKeyName functionality with proper error handling.
     */
    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /**
     * Handle getSlugAttribute functionality with proper error handling.
     */
    public function getSlugAttribute(): string
    {
        return (string) ($this->getTranslation('slug', app()->getLocale()) ?? '');
    }

    /**
     * Handle getTitleAttribute functionality with proper error handling.
     */
    public function getTitleAttribute(): string
    {
        return (string) ($this->getTranslation('title', app()->getLocale()) ?? '');
    }

    /**
     * Handle getSummaryAttribute functionality with proper error handling.
     */
    public function getSummaryAttribute(): ?string
    {
        return $this->getTranslation('summary', app()->getLocale());
    }

    /**
     * Handle getContentAttribute functionality with proper error handling.
     */
    public function getContentAttribute(): ?string
    {
        $content = $this->getTranslation('content', app()->getLocale());
        if ($content === null || ! is_string($content)) {
            return null;
        }

        return app(HtmlContentSanitizer::class)->sanitize($content);
    }

    /**
     * Handle getSeoTitleAttribute functionality with proper error handling.
     */
    public function getSeoTitleAttribute(): ?string
    {
        return $this->getTranslation('seo_title', app()->getLocale());
    }

    /**
     * Handle getSeoDescriptionAttribute functionality with proper error handling.
     */
    public function getSeoDescriptionAttribute(): ?string
    {
        return $this->getTranslation('seo_description', app()->getLocale());
    }

    /**
     * Retrieve a sanitized embed URL for the associated podcast episode.
     */
    public function getPodcastPlayerUrl(): ?string
    {
        $embedUrl = data_get($this->meta_data, 'podcast_embed_url');
        if (is_string($embedUrl) && $embedUrl !== '') {
            $normalized = $this->normalizePodcastUrl($embedUrl, true);
            if ($normalized !== null) {
                return $normalized;
            }
        }

        $shareUrl = data_get($this->meta_data, 'podcast_url');
        if (is_string($shareUrl) && $shareUrl !== '') {
            return $this->normalizePodcastUrl($shareUrl, true);
        }

        return null;
    }

    /**
     * Retrieve a sanitized share URL for the associated podcast episode.
     */
    public function getPodcastShareUrl(): ?string
    {
        $shareUrl = data_get($this->meta_data, 'podcast_url');
        if (is_string($shareUrl) && $shareUrl !== '') {
            $normalized = $this->normalizePodcastUrl($shareUrl, false);
            if ($normalized !== null) {
                return $normalized;
            }
        }

        $embedUrl = data_get($this->meta_data, 'podcast_embed_url');
        if (is_string($embedUrl) && $embedUrl !== '') {
            return $this->normalizePodcastUrl($embedUrl, false);
        }

        return null;
    }

    /**
     * Normalize supported podcast URLs while avoiding untrusted hosts.
     */
    private function normalizePodcastUrl(string $url, bool $preferEmbed): ?string
    {
        $trimmed = trim($url);
        if ($trimmed === '' || filter_var($trimmed, FILTER_VALIDATE_URL) === false) {
            return null;
        }

        $parsed = parse_url($trimmed);
        if ($parsed === false || ! isset($parsed['host'])) {
            return null;
        }

        $host = strtolower($parsed['host']);
        if (! Str::contains($host, 'transistor.fm')) {
            return null;
        }

        $path = $parsed['path'] ?? '';
        $path = '/' . ltrim($path, '/');
        if ($path === '/') {
            return null;
        }

        if ($preferEmbed) {
            if (Str::startsWith($path, '/s/')) {
                $path = Str::replaceFirst('/s/', '/e/', $path);
            } elseif (! Str::startsWith($path, '/e/')) {
                return null;
            }
        } else {
            if (Str::startsWith($path, '/e/')) {
                $path = Str::replaceFirst('/e/', '/s/', $path);
            } elseif (! Str::startsWith($path, '/s/')) {
                return null;
            }
        }

        return 'https://share.transistor.fm' . $path;
    }
}
