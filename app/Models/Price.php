<?php

declare (strict_types=1);
namespace App\Models;

use App\Models\Scopes\DateRangeScope;
use App\Models\Scopes\EnabledScope;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
/**
 * Price
 * 
 * Eloquent model representing the Price entity with comprehensive relationships, scopes, and business logic for the e-commerce system.
 * 
 * @property mixed $table
 * @property mixed $fillable
 * @method static \Illuminate\Database\Eloquent\Builder|Price newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Price newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Price query()
 * @mixin \Eloquent
 */
#[ScopedBy([EnabledScope::class, DateRangeScope::class])]
final class Price extends Model
{
    use HasFactory, SoftDeletes;
    protected $table = 'prices';
    /**
     * Eager-load defaults for admin tables/infolists to avoid N+1.
     *
     * @var array<int, string>
     */
    protected $with = ['currency'];
    protected $fillable = ['priceable_id', 'priceable_type', 'currency_id', 'amount', 'compare_amount', 'cost_amount', 'type', 'starts_at', 'ends_at', 'is_enabled', 'metadata'];
    /**
     * Handle casts functionality with proper error handling.
     * @return array
     */
    protected function casts(): array
    {
        return ['amount' => 'decimal:2', 'compare_amount' => 'decimal:2', 'cost_amount' => 'decimal:2', 'starts_at' => 'datetime', 'ends_at' => 'datetime', 'is_enabled' => 'boolean', 'metadata' => 'array'];
    }
    /**
     * Handle priceable functionality with proper error handling.
     * @return MorphTo
     */
    public function priceable(): MorphTo
    {
        return $this->morphTo();
    }
    /**
     * Handle currency functionality with proper error handling.
     * @return BelongsTo
     */
    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class);
    }
    /**
     * Handle translations functionality with proper error handling.
     * @return HasMany
     */
    public function translations(): HasMany
    {
        return $this->hasMany(\App\Models\Translations\PriceTranslation::class);
    }
    /**
     * Handle scopeEnabled functionality with proper error handling.
     * @param mixed $query
     */
    public function scopeEnabled($query)
    {
        return $query->where('is_enabled', true);
    }
    /**
     * Handle scopeActive functionality with proper error handling.
     * @param mixed $query
     */
    public function scopeActive($query)
    {
        return $query->where('is_enabled', true)->where(function ($q) {
            $q->whereNull('starts_at')->orWhere('starts_at', '<=', now());
        })->where(function ($q) {
            $q->whereNull('ends_at')->orWhere('ends_at', '>=', now());
        });
    }
    /**
     * Handle scopeForCurrency functionality with proper error handling.
     * @param mixed $query
     * @param string $currencyCode
     */
    public function scopeForCurrency($query, string $currencyCode)
    {
        return $query->whereHas('currency', function ($q) use ($currencyCode) {
            $q->where('code', $currencyCode);
        });
    }
    /**
     * Handle isActive functionality with proper error handling.
     * @return bool
     */
    public function isActive(): bool
    {
        if (!$this->is_enabled) {
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
    /**
     * Handle getDiscountPercentageAttribute functionality with proper error handling.
     * @return int|null
     */
    public function getDiscountPercentageAttribute(): ?int
    {
        if (!$this->compare_amount || $this->compare_amount <= $this->amount) {
            return null;
        }
        return (int) round(($this->compare_amount - $this->amount) / $this->compare_amount * 100);
    }
    // Translation methods
    /**
     * Handle getTranslatedName functionality with proper error handling.
     * @param string|null $locale
     * @return string|null
     */
    public function getTranslatedName(?string $locale = null): ?string
    {
        $locale = $locale ?: app()->getLocale();
        $translation = $this->translations()->where('locale', $locale)->first();
        return $translation?->name;
    }
    /**
     * Handle getTranslatedDescription functionality with proper error handling.
     * @param string|null $locale
     * @return string|null
     */
    public function getTranslatedDescription(?string $locale = null): ?string
    {
        $locale = $locale ?: app()->getLocale();
        $translation = $this->translations()->where('locale', $locale)->first();
        return $translation?->description;
    }
    /**
     * Handle getTranslatedNotes functionality with proper error handling.
     * @param string|null $locale
     * @return string|null
     */
    public function getTranslatedNotes(?string $locale = null): ?string
    {
        $locale = $locale ?: app()->getLocale();
        $translation = $this->translations()->where('locale', $locale)->first();
        return $translation?->notes;
    }
    // Scope for translated prices
    /**
     * Handle scopeWithTranslations functionality with proper error handling.
     * @param mixed $query
     * @param string|null $locale
     */
    public function scopeWithTranslations($query, ?string $locale = null)
    {
        $locale = $locale ?: app()->getLocale();
        return $query->with(['translations' => function ($q) use ($locale) {
            $q->where('locale', $locale);
        }]);
    }
    // Get all available locales for this price
    /**
     * Handle getAvailableLocales functionality with proper error handling.
     * @return array
     */
    public function getAvailableLocales(): array
    {
        return $this->translations()->pluck('locale')->toArray();
    }
    // Check if price has translation for specific locale
    /**
     * Handle hasTranslationFor functionality with proper error handling.
     * @param string $locale
     * @return bool
     */
    public function hasTranslationFor(string $locale): bool
    {
        return $this->translations()->where('locale', $locale)->exists();
    }
    // Get or create translation for locale
    /**
     * Handle getOrCreateTranslation functionality with proper error handling.
     * @param string $locale
     * @return App\Models\Translations\PriceTranslation
     */
    public function getOrCreateTranslation(string $locale): \App\Models\Translations\PriceTranslation
    {
        return $this->translations()->firstOrCreate(['locale' => $locale], ['name' => null, 'description' => null, 'notes' => null]);
    }
    // Update translation for specific locale
    /**
     * Handle updateTranslation functionality with proper error handling.
     * @param string $locale
     * @param array $data
     * @return bool
     */
    public function updateTranslation(string $locale, array $data): bool
    {
        $translation = $this->getOrCreateTranslation($locale);
        return $translation->update($data);
    }
    // Delete translation for specific locale
    /**
     * Handle deleteTranslation functionality with proper error handling.
     * @param string $locale
     * @return bool
     */
    public function deleteTranslation(string $locale): bool
    {
        return $this->translations()->where('locale', $locale)->delete() > 0;
    }
}