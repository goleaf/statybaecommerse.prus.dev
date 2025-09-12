<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

final class Price extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'prices';

    protected $fillable = [
        'priceable_id',
        'priceable_type',
        'currency_id',
        'amount',
        'compare_amount',
        'cost_amount',
        'type',
        'starts_at',
        'ends_at',
        'is_enabled',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:4',
            'compare_amount' => 'decimal:4',
            'cost_amount' => 'decimal:4',
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'is_enabled' => 'boolean',
            'metadata' => 'array',
        ];
    }

    public function priceable(): MorphTo
    {
        return $this->morphTo();
    }

    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class);
    }

    public function translations(): HasMany
    {
        return $this->hasMany(\App\Models\Translations\PriceTranslation::class);
    }

    public function scopeEnabled($query)
    {
        return $query->where('is_enabled', true);
    }

    public function scopeActive($query)
    {
        return $query
            ->where('is_enabled', true)
            ->where(function ($q) {
                $q
                    ->whereNull('starts_at')
                    ->orWhere('starts_at', '<=', now());
            })
            ->where(function ($q) {
                $q
                    ->whereNull('ends_at')
                    ->orWhere('ends_at', '>=', now());
            });
    }

    public function scopeForCurrency($query, string $currencyCode)
    {
        return $query->whereHas('currency', function ($q) use ($currencyCode) {
            $q->where('code', $currencyCode);
        });
    }

    public function isActive(): bool
    {
        if (! $this->is_enabled) {
            return false;
        }

        $now = now();

        if ($this->starts_at && $this->starts_at->gt($now)) {
            return false;
        }

        if ($this->ends_at && $this->ends_at->lt($now)) {
            return false;
        }

        return true;
    }

    public function getDiscountPercentageAttribute(): ?int
    {
        if (! $this->compare_amount || $this->compare_amount <= $this->amount) {
            return null;
        }

        return (int) round((($this->compare_amount - $this->amount) / $this->compare_amount) * 100);
    }

    // Translation methods
    public function getTranslatedName(?string $locale = null): ?string
    {
        $locale = $locale ?: app()->getLocale();
        $translation = $this->translations()->where('locale', $locale)->first();

        return $translation?->name;
    }

    public function getTranslatedDescription(?string $locale = null): ?string
    {
        $locale = $locale ?: app()->getLocale();
        $translation = $this->translations()->where('locale', $locale)->first();

        return $translation?->description;
    }

    public function getTranslatedNotes(?string $locale = null): ?string
    {
        $locale = $locale ?: app()->getLocale();
        $translation = $this->translations()->where('locale', $locale)->first();

        return $translation?->notes;
    }

    // Scope for translated prices
    public function scopeWithTranslations($query, ?string $locale = null)
    {
        $locale = $locale ?: app()->getLocale();

        return $query->with(['translations' => function ($q) use ($locale) {
            $q->where('locale', $locale);
        }]);
    }

    // Get all available locales for this price
    public function getAvailableLocales(): array
    {
        return $this->translations()->pluck('locale')->toArray();
    }

    // Check if price has translation for specific locale
    public function hasTranslationFor(string $locale): bool
    {
        return $this->translations()->where('locale', $locale)->exists();
    }

    // Get or create translation for locale
    public function getOrCreateTranslation(string $locale): \App\Models\Translations\PriceTranslation
    {
        return $this->translations()->firstOrCreate(
            ['locale' => $locale],
            [
                'name' => null,
                'description' => null,
                'notes' => null,
            ]
        );
    }

    // Update translation for specific locale
    public function updateTranslation(string $locale, array $data): bool
    {
        $translation = $this->getOrCreateTranslation($locale);

        return $translation->update($data);
    }

    // Delete translation for specific locale
    public function deleteTranslation(string $locale): bool
    {
        return $this->translations()->where('locale', $locale)->delete() > 0;
    }
}
