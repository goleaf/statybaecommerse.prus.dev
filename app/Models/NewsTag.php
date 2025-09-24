<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Scopes\ActiveScope;
use App\Traits\HasTranslations;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * NewsTag
 *
 * Eloquent model representing the NewsTag entity with comprehensive relationships, scopes, and business logic for the e-commerce system.
 *
 * @property mixed $table
 * @property mixed $fillable
 * @property string $translationModel
 *
 * @method static \Illuminate\Database\Eloquent\Builder|NewsTag newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|NewsTag newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|NewsTag query()
 *
 * @mixin \Eloquent
 */
#[ScopedBy([ActiveScope::class])]
final class NewsTag extends Model
{
    use HasFactory;
    use HasTranslations;

    protected $table = 'news_tags';

    protected $fillable = ['is_visible', 'color', 'sort_order'];

    /**
     * Handle casts functionality with proper error handling.
     */
    protected function casts(): array
    {
        return ['is_visible' => 'boolean', 'sort_order' => 'integer'];
    }

    protected string $translationModel = \App\Models\Translations\NewsTagTranslation::class;

    /**
     * Handle news functionality with proper error handling.
     */
    public function news(): BelongsToMany
    {
        return $this->belongsToMany(News::class, 'news_tag_pivot', 'news_tag_id', 'news_id')->withTimestamps();
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
