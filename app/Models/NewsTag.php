<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\HasTranslations;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

final class NewsTag extends Model
{
    use HasFactory;
    use HasTranslations;

    protected $table = 'news_tags';

    protected $fillable = [
        'is_active',
        'color',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    protected string $translationModel = \App\Models\Translations\NewsTagTranslation::class;

    public function news(): BelongsToMany
    {
        return $this->belongsToMany(News::class, 'news_tag_pivot', 'news_tag_id', 'news_id')
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
