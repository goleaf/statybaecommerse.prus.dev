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
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
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
 * @property int|null                  $tier_id
 * @property array<string, mixed>|null $metadata
 * @property-read \App\Models\PartnerTier|null $partnerTier
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

    protected $table = 'partners';

    protected $fillable = ['name', 'code', 'contact_email', 'contact_phone', 'is_enabled', 'discount_rate', 'commission_rate', 'tier_id', 'metadata'];

    /**
     * Handle casts functionality with proper error handling.
     */
    protected function casts(): array
    {
        return ['is_enabled' => 'boolean', 'discount_rate' => 'decimal:4', 'commission_rate' => 'decimal:4', 'tier_id' => 'integer', 'metadata' => 'array'];
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\PartnerTier, $this>
     */
    public function partnerTier(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(PartnerTier::class, 'tier_id');
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
