<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Scopes\ActiveScope;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

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
    use HasFactory;

    protected $fillable = ['seoable_type', 'seoable_id', 'locale', 'title', 'description', 'keywords', 'canonical_url', 'meta_tags', 'structured_data', 'no_index', 'no_follow'];

    protected $casts = ['meta_tags' => 'array', 'structured_data' => 'array', 'no_index' => 'boolean', 'no_follow' => 'boolean'];

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
            $html .= '<title>'.e($this->title).'</title>'.PHP_EOL;
            $html .= '<meta property="og:title" content="'.e($this->title).'">'.PHP_EOL;
        }
        if ($this->description) {
            $html .= '<meta name="description" content="'.e($this->description).'">'.PHP_EOL;
            $html .= '<meta property="og:description" content="'.e($this->description).'">'.PHP_EOL;
        }
        if ($this->keywords) {
            $html .= '<meta name="keywords" content="'.e($this->keywords).'">'.PHP_EOL;
        }
        if ($this->canonical_url) {
            $html .= '<link rel="canonical" href="'.e($this->canonical_url).'">'.PHP_EOL;
        }
        if ($this->no_index || $this->no_follow) {
            $robots = [];
            if ($this->no_index) {
                $robots[] = 'noindex';
            }
            if ($this->no_follow) {
                $robots[] = 'nofollow';
            }
            $html .= '<meta name="robots" content="'.implode(', ', $robots).'">'.PHP_EOL;
        }
        if ($this->meta_tags) {
            foreach ($this->meta_tags as $name => $content) {
                $html .= '<meta name="'.e($name).'" content="'.e($content).'">'.PHP_EOL;
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
            Product::class => 'Product',
            Category::class => 'Category',
            Brand::class => 'Brand',
            default => class_basename($this->seoable_type),
        };
    }

    /**
     * Handle getLocaleNameAttribute functionality with proper error handling.
     */
    public function getLocaleNameAttribute(): string
    {
        return match ($this->locale) {
            'lt' => 'Lietuvių',
            'en' => 'English',
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
        if (! $this->keywords) {
            return 0;
        }

        return count(array_filter(explode(',', $this->keywords)));
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
        if ($this->keywords) {
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
            default => 'danger',
        };
    }
}
