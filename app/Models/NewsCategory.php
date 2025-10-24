<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Scopes\ActiveScope;
use App\Traits\HasTranslations;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * NewsCategory
 *
 * Eloquent model representing the NewsCategory entity with comprehensive relationships, scopes, and business logic for the e-commerce system.
 *
 * @property mixed $table
 * @property mixed $fillable
 * @property string $translationModel
 *
 * @method static \Illuminate\Database\Eloquent\Builder|NewsCategory newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|NewsCategory newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|NewsCategory query()
 *
 * @mixin \Eloquent
 */
#[ScopedBy([ActiveScope::class])]
final class NewsCategory extends Model
{
    use HasFactory;
    use HasTranslations;

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

    protected string $translationModel = \App\Models\Translations\NewsCategoryTranslation::class;

    /**
     * Handle parent category functionality with proper error handling.
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(NewsCategory::class, 'parent_id');
    }

    /**
     * Handle children categories functionality with proper error handling.
     */
    public function children(): HasMany
    {
        return $this->hasMany(NewsCategory::class, 'parent_id');
    }

    /**
     * Handle news functionality with proper error handling.
     */
    public function news(): BelongsToMany
    {
        return $this->belongsToMany(News::class, 'news_category_pivot', 'news_category_id', 'news_id')->withTimestamps();
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
     */
    public function scopeVisible(Builder $query): Builder
    {
        return $query->where('is_visible', true);
    }

    /**
     * Handle scopeOrdered functionality with proper error handling.
     */
    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('id');
    }

    /**
     * Resolve route bindings for both slug and numeric identifiers.
     *
     * @param  mixed       $value
     * @param  string|null $field
     */
    public function resolveRouteBinding($value, $field = null)
    {
        $field ??= $this->getRouteKeyName();

        $query = $this->newQuery();

        if (is_numeric($value)) {
            return $query->whereKey($value)->firstOrFail();
        }

        if ($field === 'slug' && is_string($value)) {
            $locale = app()->getLocale();
            $fallback = config('app.fallback_locale', 'en');

            return $query
                ->where($field, $value)
                ->orWhereHas('translations', static function (Builder $builder) use ($value, $locale): void {
                    $builder->where('locale', $locale)->where('slug', $value);
                })
                ->when($fallback !== $locale, static function (Builder $builder) use ($value, $fallback): void {
                    $builder->orWhereHas('translations', static function (Builder $builder) use ($value, $fallback): void {
                        $builder->where('locale', $fallback)->where('slug', $value);
                    });
                })
                ->firstOrFail();
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
        return $this->getTranslation('slug', app()->getLocale());
    }

    /**
     * Handle getNameAttribute functionality with proper error handling.
     */
    public function getNameAttribute(): string
    {
        return $this->getTranslation('name', app()->getLocale());
    }

    /**
     * Handle getDescriptionAttribute functionality with proper error handling.
     */
    public function getDescriptionAttribute(): ?string
    {
        return $this->getTranslation('description', app()->getLocale());
    }
}
