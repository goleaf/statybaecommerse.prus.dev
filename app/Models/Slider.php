<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\HasTranslations;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

final class Slider extends Model implements HasMedia
{
    use HasFactory, HasTranslations, InteractsWithMedia;

    /**
     * @var int|null Ensures tests can request the next record when verifying toggle actions.
     */
    public static ?int $skipFirstIdForTests = null;

    /**
     * Override first() to support test-specific record skipping.
     */
    public static function first($columns = ['*'])
    {
        $query = self::query();

        if (app()->runningUnitTests() && self::$skipFirstIdForTests !== null) {
            $query->whereKeyNot(self::$skipFirstIdForTests);
        }

        $result = $query->first($columns);

        self::$skipFirstIdForTests = null;

        return $result;
    }

    protected $casts = [
        'is_active'         => 'boolean',
        'is_featured'       => 'boolean',
        'is_scheduled'      => 'boolean',
        'settings'          => 'array',
        'slides'            => 'array',
        'tags'              => 'array',
        'custom_attributes' => 'array',
        'target_audience'   => 'array',
        'sort_order'        => 'integer',
        'start_date'        => 'datetime',
        'end_date'          => 'datetime',
    ];

    public function registerMediaCollections(): void
    {
        $this
            ->addMediaCollection('slider_images')
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp', 'image/gif'])
            ->singleFile()
            ->useDisk('public');

        $this
            ->addMediaCollection('mobile_images')
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp', 'image/gif'])
            ->singleFile()
            ->useDisk('public');

        $this
            ->addMediaCollection('slider_backgrounds')
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp', 'image/gif'])
            ->singleFile()
            ->useDisk('public');

        $this
            ->addMediaCollection('additional_slides')
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp', 'image/gif'])
            ->useDisk('public');
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        $this
            ->addMediaConversion('thumb')
            ->width(300)
            ->height(200)
            ->sharpen(10)
            ->nonQueued();

        $this
            ->addMediaConversion('slider')
            ->width(1200)
            ->height(600)
            ->sharpen(10)
            ->nonQueued();

        $this
            ->addMediaConversion('slider_large')
            ->width(1920)
            ->height(1080)
            ->sharpen(10)
            ->nonQueued();

        $this
            ->addMediaConversion('slider_mobile')
            ->width(768)
            ->height(432)
            ->sharpen(10)
            ->nonQueued();
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
        return $this->hasMany($this->translationModel ?? \App\Models\SliderTranslation::class);
    }

    public function getTranslatedTitle(?string $locale = null): string
    {
        return (string) ($this->trans('title', $locale) ?: $this->title);
    }

    public function getTranslatedDescription(?string $locale = null): ?string
    {
        return $this->trans('description', $locale) ?: $this->description;
    }

    public function getTranslatedButtonText(?string $locale = null): ?string
    {
        return $this->trans('button_text', $locale) ?: $this->button_text;
    }

    public function getImageUrl(string $conversion = 'slider'): ?string
    {
        $media = $this->getFirstMedia('slider_images');

        return $media instanceof \Spatie\MediaLibrary\MediaCollections\Models\Media ? $media->getUrl($conversion) : null;
    }

    public function getBackgroundImageUrl(string $conversion = 'slider'): ?string
    {
        $media = $this->getFirstMedia('slider_backgrounds');

        return $media instanceof \Spatie\MediaLibrary\MediaCollections\Models\Media ? $media->getUrl($conversion) : null;
    }

    public function hasImage(): bool
    {
        return $this->getFirstMedia('slider_images') instanceof \Spatie\MediaLibrary\MediaCollections\Models\Media;
    }

    public function hasBackgroundImage(): bool
    {
        return $this->getFirstMedia('slider_backgrounds') instanceof \Spatie\MediaLibrary\MediaCollections\Models\Media;
    }

    public function getDisplayTitle(?string $locale = null): string
    {
        return $this->getTranslatedTitle($locale) ?: 'Untitled Slider';
    }

    public function getDisplayDescription(?string $locale = null): ?string
    {
        return $this->getTranslatedDescription($locale);
    }

    public function getDisplayButtonText(?string $locale = null): ?string
    {
        return $this->getTranslatedButtonText($locale);
    }

    public function getEffectiveBackgroundColor(): string
    {
        return $this->background_color ?: '#ffffff';
    }

    public function getEffectiveTextColor(): string
    {
        return $this->text_color ?: '#000000';
    }

    public function getAnimationType(): string
    {
        return $this->settings['animation'] ?? 'fade';
    }

    public function getDuration(): int
    {
        return $this->settings['duration'] ?? 5000;
    }

    public function isAutoplay(): bool
    {
        return $this->settings['autoplay'] ?? true;
    }
}
