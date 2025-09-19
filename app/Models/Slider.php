<?php declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

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
        $this
            ->addMediaCollection('slider_images')
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp', 'image/gif'])
            ->singleFile()
            ->useDisk('public');

        $this
            ->addMediaCollection('slider_backgrounds')
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp', 'image/gif'])
            ->singleFile()
            ->useDisk('public');
    }

    public function registerMediaConversions(Media $media = null): void
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

    public function getImageUrl(string $conversion = 'slider'): ?string
    {
        $media = $this->getFirstMedia('slider_images');
        return $media ? $media->getUrl($conversion) : null;
    }

    public function getBackgroundImageUrl(string $conversion = 'slider'): ?string
    {
        $media = $this->getFirstMedia('slider_backgrounds');
        return $media ? $media->getUrl($conversion) : null;
    }

    public function hasImage(): bool
    {
        return $this->getFirstMedia('slider_images') !== null;
    }

    public function hasBackgroundImage(): bool
    {
        return $this->getFirstMedia('slider_backgrounds') !== null;
    }

    public function getDisplayTitle(string $locale = null): string
    {
        return $this->getTranslatedTitle($locale) ?: 'Untitled Slider';
    }

    public function getDisplayDescription(string $locale = null): ?string
    {
        return $this->getTranslatedDescription($locale);
    }

    public function getDisplayButtonText(string $locale = null): ?string
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
