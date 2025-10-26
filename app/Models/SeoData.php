<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\OrdersByName;
use App\Models\Scopes\ActiveScope;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use JsonSerializable;
use Spatie\Translatable\HasTranslations;
use Stringable;

/**
 * SeoData
 *
 * Eloquent model representing the SeoData entity with comprehensive relationships, scopes, and business logic for the e-commerce system.
 *
 * @property mixed $fillable
 * @property mixed $casts
 *
 * @method static \Illuminate\Database\Eloquent\Builder|SeoData newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|SeoData newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|SeoData query()
 *
 * @mixin \Eloquent
 */
#[ScopedBy([ActiveScope::class])]
final class SeoData extends Model
{
    use HasFactory, HasTranslations, OrdersByName, SoftDeletes;

    /**
     * Column leveraged by the shared OrdersByName scope for alphabetical sorting.
     */
    protected string $nameColumn = 'title';

    /**
     * @var array<int, string>
     */
    protected $fillable = [
        'seoable_type',
        'seoable_id',
        'title',
        'description',
        'keywords',
        'slug',
        'meta',
        'locale',
        'canonical_url',
        'meta_tags',
        'structured_data',
        'no_index',
        'no_follow',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'keywords'        => 'array',
        'meta'            => 'array',
        'meta_tags'       => 'array',
        'structured_data' => 'array',
        'no_index'        => 'boolean',
        'no_follow'       => 'boolean',
    ];

    public array $translatable = ['title', 'description'];

    protected static function booted(): void
    {
        self::creating(function (self $model): void {
            // Default locale to lt
            if (empty($model->locale)) {
                $model->locale = 'lt';
            }

            // Map friendly fields if provided
            if (array_key_exists('url', $model->attributes) && ! empty($model->attributes['url'])) {
                $model->canonical_url = (string) $model->attributes['url'];
            }
            if (array_key_exists('is_indexed', $model->attributes)) {
                $model->no_index = ! (bool) $model->attributes['is_indexed'];
                unset($model->attributes['is_indexed']);
            }

            // Ensure translatable fields are stored as translations for current locale
            foreach (['title', 'description'] as $attr) {
                $value = $model->getAttribute($attr);
                if (is_string($value)) {
                    $model->setTranslation($attr, $model->locale ?? app()->getLocale() ?? 'lt', $value);
                }
            }

            // Normalise keyword payloads so they persist as JSON arrays for casting.
            if (array_key_exists('keywords', $model->attributes)) {
                $model->keywords = $model->attributes['keywords'];
            }

            // Allow detached records (not morphing to another model)
            if (! array_key_exists('seoable_type', $model->attributes)) {
                $model->seoable_type = 'page';
            }
            if (! array_key_exists('seoable_id', $model->attributes)) {
                $model->seoable_id = null;
            }
        });
    }

    /**
     * Normalise the keywords attribute so callers always receive a trimmed array.
     */
    protected function keywords(): Attribute
    {
        return Attribute::make(
            get: fn (mixed $value): array => $this->normalizeKeywordsValue($value),
            set: fn (mixed $value): array => ['keywords' => $this->normalizeKeywordsValue($value)],
        );
    }

    /**
     * Provide a convenient alias for the legacy meta_tags column expected by the new API.
     */
    protected function meta(): Attribute
    {
        return Attribute::make(
            get: fn (mixed $value, array $attributes): array => $this->normalizeMetaValue($value ?? ($attributes['meta_tags'] ?? null)),
            set: fn (mixed $value): array => ['meta_tags' => $this->normalizeMetaValue($value)],
        );
    }

    /**
     * Provide a defensive slug attribute without persisting an unavailable column.
     */
    protected function slug(): Attribute
    {
        return Attribute::make(
            get: fn (mixed $value): ?string => is_string($value) ? $value : null,
            set: fn (mixed $value): array => [],
        );
    }

    /**
     * Reduce the keyword payload to a human-readable string for display or meta tags.
     */
    public function keywordsAsString(string $separator = ', '): string
    {
        return implode($separator, $this->keywords);
    }

    /**
     * Normalise raw keyword payloads into a trimmed list of strings.
     *
     * @return array<int, string>
     */
    private function normalizeKeywordsValue(mixed $value): array
    {
        if ($value instanceof Stringable) {
            $value = (string) $value;
        }

        if (is_array($value)) {
            return array_values(array_filter(
                array_map(static fn ($item): string => trim((string) $item), $value),
                static fn ($item): bool => $item !== ''
            ));
        }

        if (is_string($value) && $value !== '') {
            $decoded = json_decode($value, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                return $this->normalizeKeywordsValue($decoded);
            }

            return $this->normalizeKeywordsValue(explode(',', $value));
        }

        return [];
    }

    /**
     * Cast assorted meta representations into an array for consistent downstream handling.
     *
     * @return array<string, mixed>
     */
    private function normalizeMetaValue(mixed $value): array
    {
        if ($value instanceof JsonSerializable) {
            $value = $value->jsonSerialize();
        }

        if (is_string($value) && $value !== '') {
            $decoded = json_decode($value, true);

            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                return $decoded;
            }

            return [];
        }

        if (is_array($value)) {
            return $value;
        }

        return [];
    }

    /**
     * Handle seoable functionality with proper error handling.
     */
    public function seoable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Handle scopeForLocale functionality with proper error handling.
     */
    public function scopeForLocale(Builder $query, string $locale): Builder
    {
        return $query->where('locale', $locale);
    }

    /**
     * Handle scopeByType functionality with proper error handling.
     */
    public function scopeByType(Builder $query, string $type): Builder
    {
        return $query->where('seoable_type', $type);
    }

    /**
     * Handle scopeIndexable functionality with proper error handling.
     */
    public function scopeIndexable(Builder $query): Builder
    {
        return $query->where('no_index', false);
    }

    /**
     * Handle scopeFollowable functionality with proper error handling.
     */
    public function scopeFollowable(Builder $query): Builder
    {
        return $query->where('no_follow', false);
    }

    /**
     * Handle scopeWithTitle functionality with proper error handling.
     */
    public function scopeWithTitle(Builder $query): Builder
    {
        return $query->whereNotNull('title');
    }

    /**
     * Handle scopeWithDescription functionality with proper error handling.
     */
    public function scopeWithDescription(Builder $query): Builder
    {
        return $query->whereNotNull('description');
    }

    /**
     * Handle scopeWithKeywords functionality with proper error handling.
     */
    public function scopeWithKeywords(Builder $query): Builder
    {
        return $query->whereNotNull('keywords');
    }

    /**
     * Handle scopeWithCanonicalUrl functionality with proper error handling.
     */
    public function scopeWithCanonicalUrl(Builder $query): Builder
    {
        return $query->whereNotNull('canonical_url');
    }

    /**
     * Handle scopeWithStructuredData functionality with proper error handling.
     */
    public function scopeWithStructuredData(Builder $query): Builder
    {
        return $query->whereNotNull('structured_data');
    }

    /**
     * Handle scopeForProducts functionality with proper error handling.
     */
    public function scopeForProducts(Builder $query): Builder
    {
        return $query->where('seoable_type', Product::class);
    }

    /**
     * Handle scopeForCategories functionality with proper error handling.
     */
    public function scopeForCategories(Builder $query): Builder
    {
        return $query->where('seoable_type', Category::class);
    }

    /**
     * Handle scopeForBrands functionality with proper error handling.
     */
    public function scopeForBrands(Builder $query): Builder
    {
        return $query->where('seoable_type', Brand::class);
    }

    /**
     * Handle getMetaTagsHtmlAttribute functionality with proper error handling.
     */
    public function getMetaTagsHtmlAttribute(): string
    {
        $html = '';
        if ($this->title) {
            $html .= '<title>' . e($this->title) . '</title>' . PHP_EOL;
            $html .= '<meta property="og:title" content="' . e($this->title) . '">' . PHP_EOL;
        }
        if ($this->description) {
            $html .= '<meta name="description" content="' . e($this->description) . '">' . PHP_EOL;
            $html .= '<meta property="og:description" content="' . e($this->description) . '">' . PHP_EOL;
        }
        $keywordsString = $this->keywordsAsString();
        if ($keywordsString !== '') {
            $html .= '<meta name="keywords" content="' . e($keywordsString) . '">' . PHP_EOL;
        }
        if ($this->canonical_url) {
            $html .= '<link rel="canonical" href="' . e($this->canonical_url) . '">' . PHP_EOL;
        }
        if ($this->no_index || $this->no_follow) {
            $robots = [];
            if ($this->no_index) {
                $robots[] = 'noindex';
            }
            if ($this->no_follow) {
                $robots[] = 'nofollow';
            }
            $html .= '<meta name="robots" content="' . implode(', ', $robots) . '">' . PHP_EOL;
        }
        if ($this->meta_tags) {
            foreach ($this->meta_tags as $name => $content) {
                $html .= '<meta name="' . e($name) . '" content="' . e($content) . '">' . PHP_EOL;
            }
        }

        return $html;
    }

    /**
     * Handle getStructuredDataJsonAttribute functionality with proper error handling.
     */
    public function getStructuredDataJsonAttribute(): ?string
    {
        if (! $this->structured_data) {
            return null;
        }

        return json_encode($this->structured_data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }

    /**
     * Handle getSeoableNameAttribute functionality with proper error handling.
     */
    public function getSeoableNameAttribute(): ?string
    {
        return $this->seoable?->name ?? $this->seoable?->title ?? null;
    }

    /**
     * Handle getSeoableTypeNameAttribute functionality with proper error handling.
     */
    public function getSeoableTypeNameAttribute(): string
    {
        return match ($this->seoable_type) {
            Product::class  => 'Product',
            Category::class => 'Category',
            Brand::class    => 'Brand',
            default         => class_basename($this->seoable_type),
        };
    }

    /**
     * Handle getLocaleNameAttribute functionality with proper error handling.
     */
    public function getLocaleNameAttribute(): string
    {
        return match ($this->locale) {
            'lt'    => 'Lietuvių',
            'en'    => 'English',
            default => strtoupper($this->locale),
        };
    }

    /**
     * Handle getRobotsAttribute functionality with proper error handling.
     */
    public function getRobotsAttribute(): string
    {
        $robots = [];
        if ($this->no_index) {
            $robots[] = 'noindex';
        }
        if ($this->no_follow) {
            $robots[] = 'nofollow';
        }

        return empty($robots) ? 'index, follow' : implode(', ', $robots);
    }

    /**
     * Handle getTitleLengthAttribute functionality with proper error handling.
     */
    public function getTitleLengthAttribute(): int
    {
        return mb_strlen($this->title ?? '');
    }

    /**
     * Handle getDescriptionLengthAttribute functionality with proper error handling.
     */
    public function getDescriptionLengthAttribute(): int
    {
        return mb_strlen($this->description ?? '');
    }

    /**
     * Handle getKeywordsCountAttribute functionality with proper error handling.
     */
    public function getKeywordsCountAttribute(): int
    {
        if ($this->keywords === []) {
            return 0;
        }

        return count($this->keywords);
    }

    /**
     * Handle isTitleOptimal functionality with proper error handling.
     */
    public function isTitleOptimal(): bool
    {
        $length = $this->title_length;

        return $length >= 30 && $length <= 60;
    }

    /**
     * Handle isDescriptionOptimal functionality with proper error handling.
     */
    public function isDescriptionOptimal(): bool
    {
        $length = $this->description_length;

        return $length >= 120 && $length <= 160;
    }

    /**
     * Handle getSeoScoreAttribute functionality with proper error handling.
     */
    public function getSeoScoreAttribute(): int
    {
        $score = 0;
        // Title score (40 points max)
        if ($this->title) {
            $score += 20;
            // Has title
            if ($this->isTitleOptimal()) {
                $score += 20;
                // Optimal length
            }
        }
        // Description score (30 points max)
        if ($this->description) {
            $score += 15;
            // Has description
            if ($this->isDescriptionOptimal()) {
                $score += 15;
                // Optimal length
            }
        }
        // Keywords score (15 points max)
        if ($this->keywords !== []) {
            $score += 10;
            // Has keywords
            if ($this->keywords_count >= 3 && $this->keywords_count <= 10) {
                $score += 5;
                // Optimal count
            }
        }
        // Canonical URL score (10 points max)
        if ($this->canonical_url) {
            $score += 10;
        }
        // Structured data score (5 points max)
        if ($this->structured_data) {
            $score += 5;
        }

        return min($score, 100);
    }

    /**
     * Handle getSeoScoreColorAttribute functionality with proper error handling.
     */
    public function getSeoScoreColorAttribute(): string
    {
        return match (true) {
            $this->seo_score >= 80 => 'success',
            $this->seo_score >= 60 => 'warning',
            default                => 'danger',
        };
    }
}
