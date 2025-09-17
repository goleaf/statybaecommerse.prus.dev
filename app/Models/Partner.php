<?php

declare (strict_types=1);
namespace App\Models;

use App\Models\Scopes\ActiveScope;
use App\Models\Scopes\EnabledScope;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
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
 * @method static \Illuminate\Database\Eloquent\Builder|Partner newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Partner newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Partner query()
 * @mixin \Eloquent
 */
#[ScopedBy([ActiveScope::class, EnabledScope::class])]
final class Partner extends Model implements HasMedia
{
    use HasFactory, SoftDeletes;
    use InteractsWithMedia;
    protected $table = 'partners';
    protected $fillable = ['name', 'code', 'tier_id', 'contact_email', 'contact_phone', 'is_enabled', 'discount_rate', 'commission_rate', 'metadata'];
    /**
     * Handle casts functionality with proper error handling.
     * @return array
     */
    protected function casts(): array
    {
        return ['is_enabled' => 'boolean', 'discount_rate' => 'decimal:2', 'commission_rate' => 'decimal:2', 'metadata' => 'array'];
    }
    /**
     * Handle tier functionality with proper error handling.
     * @return BelongsTo
     */
    public function tier(): BelongsTo
    {
        return $this->belongsTo(PartnerTier::class);
    }
    /**
     * Handle users functionality with proper error handling.
     * @return BelongsToMany
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'partner_users');
    }
    /**
     * Handle priceLists functionality with proper error handling.
     * @return BelongsToMany
     */
    public function priceLists(): BelongsToMany
    {
        return $this->belongsToMany(PriceList::class, 'partner_price_list', 'partner_id', 'price_list_id');
    }
    /**
     * Handle orders functionality with proper error handling.
     * @return HasMany
     */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }
    /**
     * Handle variantInventories functionality with proper error handling.
     * @return HasMany
     */
    public function variantInventories(): HasMany
    {
        return $this->hasMany(VariantInventory::class, 'supplier_id');
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
     * Handle scopeByTier functionality with proper error handling.
     * @param mixed $query
     * @param int $tierId
     */
    public function scopeByTier($query, int $tierId)
    {
        return $query->where('tier_id', $tierId);
    }
    /**
     * Handle getEffectiveDiscountRateAttribute functionality with proper error handling.
     * @return float
     */
    public function getEffectiveDiscountRateAttribute(): float
    {
        return $this->discount_rate ?: $this->tier->discount_rate ?? 0;
    }
    /**
     * Handle getLogoUrl functionality with proper error handling.
     * @param string|null $size
     * @return string|null
     */
    public function getLogoUrl(?string $size = null): ?string
    {
        if (!$size) {
            return $this->getFirstMediaUrl('logo') ?: null;
        }
        return $this->getFirstMediaUrl('logo', "logo-{$size}") ?: $this->getFirstMediaUrl('logo');
    }
    /**
     * Handle registerMediaCollections functionality with proper error handling.
     * @return void
     */
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('logo')->singleFile()->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/svg+xml']);
    }
    /**
     * Handle registerMediaConversions functionality with proper error handling.
     * @param Media|null $media
     * @return void
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