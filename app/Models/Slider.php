<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

final class Slider extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia;

    protected $fillable = [
        'title',
        'description',
        'button_text',
        'button_url',
        'image',
        'background_color',
        'text_color',
        'sort_order',
        'is_active',
        'settings',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'settings' => 'array',
        'sort_order' => 'integer',
    ];

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('images')
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp'])
            ->singleFile();
    }

    public function registerMediaConversions(Media $media = null): void
    {
        $this->addMediaConversion('thumb')
            ->width(300)
            ->height(200)
            ->sharpen(10);

        $this->addMediaConversion('slider')
            ->width(1200)
            ->height(600)
            ->sharpen(10);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('created_at');
    }

    public function translations(): HasMany
    {
        return $this->hasMany(SliderTranslation::class);
    }

    public function translation(string $locale = null): ?SliderTranslation
    {
        $locale = $locale ?: app()->getLocale();
        return $this->translations()->where('locale', $locale)->first();
    }

    public function getTranslatedTitle(string $locale = null): string
    {
        $translation = $this->translation($locale);
        return $translation?->title ?? $this->title;
    }

    public function getTranslatedDescription(string $locale = null): ?string
    {
        $translation = $this->translation($locale);
        return $translation?->description ?? $this->description;
    }

    public function getTranslatedButtonText(string $locale = null): ?string
    {
        $translation = $this->translation($locale);
        return $translation?->button_text ?? $this->button_text;
    }
}
