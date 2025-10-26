<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Scopes\ActiveScope;
use App\Models\Scopes\EnabledScope;
use Database\Factories\PartnerFactory;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

/**
 * Partner
 *
 * Eloquent model representing the Partner entity with comprehensive relationships, scopes, and business logic for the e-commerce system.
 *
 * @property mixed $table
 * @property mixed $fillable
 *
 * @method static \Illuminate\Database\Eloquent\Builder|Partner newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Partner newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Partner query()
 *
 * @property bool                      $is_enabled
 * @property float|null                $discount_rate
 * @property float|null                $commission_rate
 * @property array<string, mixed>|null $metadata
 * @property-read \App\Models\PartnerTier|null $tier
 * @property-read float $effective_discount_rate
 * @property-read float $effective_commission_rate
 *
 * @mixin \Eloquent
 */
/**
 * @use HasFactory<PartnerFactory>
 */
#[ScopedBy([ActiveScope::class, EnabledScope::class])]
final class Partner extends Model implements HasMedia
{
    /** @use HasFactory<PartnerFactory> */
    use HasFactory;

    use InteractsWithMedia;
    use SoftDeletes;

    protected $table = 'partners';

    protected $fillable = ['name', 'code', 'tier_id', 'contact_email', 'contact_phone', 'is_enabled', 'discount_rate', 'commission_rate', 'metadata'];

    /**
     * Handle casts functionality with proper error handling.
     */
    protected function casts(): array
    {
        return ['is_enabled' => 'boolean', 'discount_rate' => 'decimal:4', 'commission_rate' => 'decimal:4', 'metadata' => 'array'];
    }

    /**
     * Handle tier functionality with proper error handling.
     *
     * @return BelongsTo<PartnerTier, $this>
     */
    public function tier(): BelongsTo
    {
        return $this->belongsTo(PartnerTier::class);
    }

    /**
     * Handle users functionality with proper error handling.
     *
     * @return BelongsToMany<User, $this>
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'partner_users');
    }

    /**
     * Handle priceLists functionality with proper error handling.
     *
     * @return BelongsToMany<PriceList, $this>
     */
    public function priceLists(): BelongsToMany
    {
        return $this->belongsToMany(PriceList::class, 'partner_price_list', 'partner_id', 'price_list_id');
    }

    /**
     * Handle orders functionality with proper error handling.
     *
     * @return HasMany<Order, $this>
     */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    /**
     * Handle variantInventories functionality with proper error handling.
     *
     * @return HasMany<VariantInventory, $this>
     */
    public function variantInventories(): HasMany
    {
        return $this->hasMany(VariantInventory::class, 'supplier_id');
    }

    /**
     * Handle apiKeys functionality with proper error handling.
     *
     * @return HasMany<ApiKey, $this>
     */
    public function apiKeys(): HasMany
    {
        return $this->hasMany(ApiKey::class);
    }

    /**
     * Handle scopeEnabled functionality with proper error handling.
     *
     * @param  Builder<static> $query
     * @return Builder<static>
     */
    public function scopeEnabled(Builder $query): Builder
    {
        // Constrain the query to only include partners that are currently enabled.
        return $query->where('is_enabled', true);
    }

    /**
     * Handle scopeByTier functionality with proper error handling.
     *
     * @param  Builder<static> $query
     * @return Builder<static>
     */
    public function scopeByTier(Builder $query, int $tierId): Builder
    {
        // Limit the query to partners assigned to the specified tier identifier.
        return $query->where('tier_id', $tierId);
    }

    /**
     * Handle scopeOrderedByName functionality with proper error handling.
     *
     * @param  Builder<static> $query
     * @return Builder<static>
     */
    public function scopeOrderedByName(Builder $query): Builder
    {
        // Apply an ascending sort on the partner name to ensure predictable listings.
        return $query->orderBy('name');
    }

    /**
     * Handle getEffectiveDiscountRateAttribute functionality with proper error handling.
     */
    public function getEffectiveDiscountRateAttribute(): float
    {
        // Prefer the partner-specific discount when available and fall back to the related tier rate.
        $ownRate = $this->getAttribute('discount_rate');
        if (is_numeric($ownRate)) {
            return (float) $ownRate;
        }

        // Lazily resolve the tier relationship so persisted partners without eager loading still inherit tier discounts.
        $tier = $this->resolveTierForFallback();
        if ($tier instanceof PartnerTier) {
            $tierRate = $tier->getAttribute('discount_rate');

            if (is_numeric($tierRate)) {
                return (float) $tierRate;
            }
        }

        return 0.0;
    }

    /**
     * Handle getEffectiveCommissionRateAttribute functionality with proper error handling.
     */
    public function getEffectiveCommissionRateAttribute(): float
    {
        // Prefer the partner-specific commission rate before falling back to the linked tier configuration.
        $ownRate = $this->getAttribute('commission_rate');
        if (is_numeric($ownRate)) {
            return (float) $ownRate;
        }

        // Resolve the tier relation lazily so accessors work even when the relationship was not eager loaded.
        $tier = $this->resolveTierForFallback();
        if ($tier instanceof PartnerTier) {
            $tierRate = $tier->getAttribute('commission_rate');

            if (is_numeric($tierRate)) {
                return (float) $tierRate;
            }
        }

        return 0.0;
    }

    /**
     * Resolve the tier relationship with a lazy-loading fallback for accessor usage.
     */
    private function resolveTierForFallback(): ?PartnerTier
    {
        // Return the already loaded relation when available to avoid redundant database queries.
        $tier = $this->getRelationValue('tier');
        if ($tier instanceof PartnerTier) {
            return $tier;
        }

        // Attempt to lazy load the relation when a foreign key is present but the relation was not eager loaded.
        $resolved = $this->tier()->getResults();
        if ($resolved instanceof PartnerTier) {
            $this->setRelation('tier', $resolved);

            return $resolved;
        }

        return null;
    }

    /**
     * Handle getLogoUrl functionality with proper error handling.
     */
    public function getLogoUrl(?string $size = null): ?string
    {
        if (! $size) {
            return $this->getFirstMediaUrl('logo') ?: null;
        }

        return $this->getFirstMediaUrl('logo', "logo-{$size}") ?: $this->getFirstMediaUrl('logo');
    }

    /**
     * Handle registerMediaCollections functionality with proper error handling.
     */
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('logo')->singleFile()->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/svg+xml']);
    }

    /**
     * Handle registerMediaConversions functionality with proper error handling.
     */
    public function registerMediaConversions(?Media $media = null): void
    {
        // Logo conversions with multiple resolutions
        $this->addMediaConversion('logo-xs')->performOnCollections('logo')->width(64)->height(64)->format('webp')->quality(85)->sharpen(10)->optimize();
        $this->addMediaConversion('logo-sm')->performOnCollections('logo')->width(128)->height(128)->format('webp')->quality(85)->sharpen(10)->optimize();
        $this->addMediaConversion('logo-md')->performOnCollections('logo')->width(200)->height(200)->format('webp')->quality(85)->sharpen(10)->optimize();
        $this->addMediaConversion('logo-lg')->performOnCollections('logo')->width(400)->height(400)->format('webp')->quality(90)->sharpen(5)->optimize();
    }
}
