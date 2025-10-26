<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\OrdersByName;
use App\Traits\HasTranslations;
use Database\Factories\NewsCategoryFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

/**
 * NewsCategory
 *
 * Eloquent model representing the NewsCategory entity with comprehensive relationships, scopes, and business logic for the e-commerce system.
 *
 * @property mixed  $table
 * @property mixed  $fillable
 * @property string $translationModel
 *
 * @method static \Illuminate\Database\Eloquent\Builder|NewsCategory newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|NewsCategory newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|NewsCategory query()
 *
 * @mixin \Eloquent
 */
final class NewsCategory extends Model
{
    /** @use HasFactory<NewsCategoryFactory> */
    use HasFactory;

    use HasTranslations;
    use OrdersByName; // Keep news categories alphabetically ordered for selection lists.

    protected $table = 'news_categories';

    protected $fillable = [
        'name',
        'slug',
        'description',
        'is_visible',
        'parent_id',
        'sort_order',
        'color',
        'icon',
    ];

    /**
     * Handle casts functionality with proper error handling.
     */
    protected function casts(): array
    {
        return ['is_visible' => 'boolean', 'sort_order' => 'integer'];
    }

    protected static function booted(): void
    {
        self::saving(static function (self $category): void {
            if (! empty($category->slug)) {
                return;
            }

            // Retrieve the name attribute, falling back to an empty string when unavailable for predictable slugs.
            $name = $category->getAttribute('name');
            $category->slug = Str::slug(is_string($name) ? $name : '');
        });
    }

    protected string $translationModel = \App\Models\Translations\NewsCategoryTranslation::class;

    /**
     * Handle parent category functionality with proper error handling.
     *
     * @return BelongsTo<NewsCategory, NewsCategory>
     */
    public function parent(): BelongsTo
    {
        /** @var BelongsTo<NewsCategory, NewsCategory> $relation */
        $relation = $this->belongsTo(NewsCategory::class, 'parent_id');

        return $relation;
    }

    /**
     * Handle children categories functionality with proper error handling.
     *
     * @return HasMany<NewsCategory, NewsCategory>
     */
    public function children(): HasMany
    {
        /** @var HasMany<NewsCategory, NewsCategory> $relation */
        $relation = $this->hasMany(NewsCategory::class, 'parent_id');

        return $relation;
    }

    /**
     * Handle news functionality with proper error handling.
     *
     * @return BelongsToMany<News, NewsCategory, \Illuminate\Database\Eloquent\Relations\Pivot, 'pivot'>
     */
    public function news(): BelongsToMany
    {
        /** @var BelongsToMany<News, NewsCategory, \Illuminate\Database\Eloquent\Relations\Pivot, 'pivot'> $relation */
        $relation = $this->belongsToMany(News::class, 'news_category_pivot', 'news_category_id', 'news_id')->withTimestamps();

        return $relation;
    }

    /**
     * Handle isVisible functionality with proper error handling.
     */
    public function isVisible(): bool
    {
        return (bool) $this->is_visible;
    }

    /**
     * Handle scopeVisible functionality with proper error handling.
     *
     * @param  Builder<self> $query
     * @return Builder<self>
     */
    public function scopeVisible(Builder $query): Builder
    {
        return $query->where('is_visible', true);
    }

    /**
     * Handle scopeOrdered functionality with proper error handling.
     *
     * @param  Builder<self> $query
     * @return Builder<self>
     */
    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('id');
    }

    /**
     * Resolve route bindings for both slug and numeric identifiers.
     *
     * @param mixed       $value
     * @param string|null $field
     */
    public function resolveRouteBinding($value, $field = null)
    {
        $field ??= $this->getRouteKeyName();

        $query = $this->newQuery();

        if ($field === 'slug' && is_string($value)) {
            return $query->where('slug', $value)->firstOrFail();
        }

        if (is_numeric($value)) {
            return $query->whereKey($value)->firstOrFail();
        }

        return parent::resolveRouteBinding($value, $field);
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
        // Retrieve the localized slug and default to an empty string when the translation is missing.
        $value = $this->getTranslation('slug', app()->getLocale());

        return is_string($value) ? $value : '';
    }

    /**
     * Handle getNameAttribute functionality with proper error handling.
     */
    public function getNameAttribute(): string
    {
        // Ensure downstream consumers always receive a string representation of the translated name.
        $value = $this->getTranslation('name', app()->getLocale());

        return is_string($value) ? $value : '';
    }

    /**
     * Handle getDescriptionAttribute functionality with proper error handling.
     */
    public function getDescriptionAttribute(): ?string
    {
        // Normalize the translated description into a nullable string for consistent usage patterns.
        $value = $this->getTranslation('description', app()->getLocale());

        return is_string($value) ? $value : null;
    }

    /**
     * Default ordering column consumed by the shared OrdersByName scope.
     */
    protected string $nameColumn = 'name';
}
