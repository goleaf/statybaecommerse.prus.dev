<?php

declare (strict_types=1);
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
 * @method static \Illuminate\Database\Eloquent\Builder|NewsTag newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|NewsTag newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|NewsTag query()
 * @mixin \Eloquent
 */
#[ScopedBy([ActiveScope::class])]
final class NewsTag extends Model
{
    use HasFactory;
    use HasTranslations;
    protected $table = 'news_tags';
    protected $fillable = ['is_active', 'color'];
    /**
     * Handle casts functionality with proper error handling.
     * @return array
     */
    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }
    protected string $translationModel = \App\Models\Translations\NewsTagTranslation::class;
    /**
     * Handle news functionality with proper error handling.
     * @return BelongsToMany
     */
    public function news(): BelongsToMany
    {
        return $this->belongsToMany(News::class, 'news_tag_pivot', 'news_tag_id', 'news_id')->withTimestamps();
    }
    /**
     * Handle isActive functionality with proper error handling.
     * @return bool
     */
    public function isActive(): bool
    {
        return (bool) $this->is_active;
    }
    /**
     * Handle scopeActive functionality with proper error handling.
     * @param Builder $query
     * @return Builder
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
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
     * Handle getNameAttribute functionality with proper error handling.
     * @return string
     */
    public function getNameAttribute(): string
    {
        return $this->getTranslation('name', app()->getLocale());
    }
    /**
     * Handle getDescriptionAttribute functionality with proper error handling.
     * @return string|null
     */
    public function getDescriptionAttribute(): ?string
    {
        return $this->getTranslation('description', app()->getLocale());
    }
}