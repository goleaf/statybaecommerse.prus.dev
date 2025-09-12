<?php declare(strict_types=1);

namespace App\Models;

use App\Traits\HasTranslations;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

final class NewsCategory extends Model
{
    use HasFactory;
    use HasTranslations;

    protected $table = 'news_categories';

    protected $fillable = [
        'is_active',
        'sort_order',
        'color',
        'icon',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    protected string $translationModel = \App\Models\Translations\NewsCategoryTranslation::class;

    public function news(): BelongsToMany
    {
        return $this->belongsToMany(News::class, 'news_category_pivot', 'news_category_id', 'news_id')
            ->withTimestamps();
    }

    public function isActive(): bool
    {
        return (bool) $this->is_active;
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('id');
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function getSlugAttribute(): string
    {
        return $this->getTranslation('slug', app()->getLocale());
    }

    public function getNameAttribute(): string
    {
        return $this->getTranslation('name', app()->getLocale());
    }

    public function getDescriptionAttribute(): ?string
    {
        return $this->getTranslation('description', app()->getLocale());
    }
}