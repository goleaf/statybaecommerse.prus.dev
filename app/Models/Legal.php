<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\HasTranslations;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Carbon\Carbon;

final class Legal extends Model
{
    use HasFactory, HasTranslations;

    protected $table = 'legals';

    protected $fillable = [
        'key',
        'type',
        'is_enabled',
        'is_required',
        'sort_order',
        'meta_data',
        'published_at',
    ];

    protected $casts = [
        'is_enabled' => 'boolean',
        'is_required' => 'boolean',
        'sort_order' => 'integer',
        'meta_data' => 'array',
        'published_at' => 'datetime',
    ];

    protected string $translationModel = \App\Models\Translations\LegalTranslation::class;

    // Scopes
    public function scopeEnabled(Builder $query): Builder
    {
        return $query->where('is_enabled', true);
    }

    public function scopeRequired(Builder $query): Builder
    {
        return $query->where('is_required', true);
    }

    public function scopeByType(Builder $query, string $type): Builder
    {
        return $query->where('type', $type);
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('published_at', '<=', now());
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('created_at');
    }

    public function scopeByKey(Builder $query, string $key): Builder
    {
        return $query->where('key', $key);
    }

    // Accessors
    protected function isPublished(): Attribute
    {
        return Attribute::make(
            get: fn (): bool => $this->published_at && $this->published_at->isPast()
        );
    }

    protected function status(): Attribute
    {
        return Attribute::make(
            get: fn (): string => match (true) {
                !$this->is_enabled => 'disabled',
                !$this->is_published => 'draft',
                default => 'published'
            }
        );
    }

    // Helper methods
    public function getTranslatedTitle(?string $locale = null): ?string
    {
        return $this->trans('title', $locale);
    }

    public function getTranslatedContent(?string $locale = null): ?string
    {
        return $this->trans('content', $locale);
    }

    public function getTranslatedSlug(?string $locale = null): ?string
    {
        return $this->trans('slug', $locale);
    }

    public function getTranslatedSeoTitle(?string $locale = null): ?string
    {
        return $this->trans('seo_title', $locale);
    }

    public function getTranslatedSeoDescription(?string $locale = null): ?string
    {
        return $this->trans('seo_description', $locale);
    }

    public function getAvailableLocales(): array
    {
        return $this->translations()->pluck('locale')->toArray();
    }

    public function hasTranslationFor(string $locale): bool
    {
        return $this->translations()->where('locale', $locale)->exists();
    }

    public function getOrCreateTranslation(string $locale): \App\Models\Translations\LegalTranslation
    {
        return $this->translations()->firstOrCreate(
            ['locale' => $locale],
            [
                'title' => $this->key,
                'slug' => \Illuminate\Support\Str::slug($this->key) . '-' . $locale,
                'content' => '',
                'seo_title' => $this->key,
                'seo_description' => '',
            ]
        );
    }

    public function updateTranslation(string $locale, array $data): bool
    {
        $translation = $this->translations()->where('locale', $locale)->first();
        
        if (!$translation) {
            $translation = $this->getOrCreateTranslation($locale);
        }

        return $translation->update($data);
    }

    public function publish(): bool
    {
        return $this->update(['published_at' => now()]);
    }

    public function unpublish(): bool
    {
        return $this->update(['published_at' => null]);
    }

    public function enable(): bool
    {
        return $this->update(['is_enabled' => true]);
    }

    public function disable(): bool
    {
        return $this->update(['is_enabled' => false]);
    }

    public function makeRequired(): bool
    {
        return $this->update(['is_required' => true]);
    }

    public function makeOptional(): bool
    {
        return $this->update(['is_required' => false]);
    }

    // Static methods
    public static function getTypes(): array
    {
        return [
            'privacy_policy' => 'Privatumo politika',
            'terms_of_use' => 'Naudojimosi sąlygos',
            'refund_policy' => 'Grąžinimo politika',
            'shipping_policy' => 'Pristatymo politika',
            'cookie_policy' => 'Slapukų politika',
            'gdpr_policy' => 'GDPR politika',
            'legal_notice' => 'Teisinė informacija',
            'imprint' => 'Imprint',
            'legal_document' => 'Teisinis dokumentas',
        ];
    }

    public static function getRequiredTypes(): array
    {
        return [
            'privacy_policy',
            'terms_of_use',
        ];
    }

    public static function getByKey(string $key): ?self
    {
        return static::byKey($key)->enabled()->published()->first();
    }

    public static function getRequiredDocuments(): \Illuminate\Database\Eloquent\Collection
    {
        return static::required()->enabled()->published()->ordered()->get();
    }

    public static function getByType(string $type): \Illuminate\Database\Eloquent\Collection
    {
        return static::byType($type)->enabled()->published()->ordered()->get();
    }
}
