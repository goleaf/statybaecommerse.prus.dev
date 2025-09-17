<?php

declare (strict_types=1);
namespace App\Models;

use App\Models\Scopes\DateRangeScope;
use App\Models\Scopes\EnabledScope;
use App\Models\Translations\PriceListTranslation;
use App\Traits\HasTranslations;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
/**
 * PriceList
 * 
 * Eloquent model representing the PriceList entity with comprehensive relationships, scopes, and business logic for the e-commerce system.
 * 
 * @property string $translationModel
 * @property mixed $table
 * @property mixed $fillable
 * @method static \Illuminate\Database\Eloquent\Builder|PriceList newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PriceList newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PriceList query()
 * @mixin \Eloquent
 */
#[ScopedBy([EnabledScope::class, DateRangeScope::class])]
final class PriceList extends Model
{
    use HasFactory, HasTranslations, LogsActivity, SoftDeletes;
    protected string $translationModel = PriceListTranslation::class;
    protected $table = 'price_lists';
    protected $fillable = ['name', 'code', 'currency_id', 'zone_id', 'is_enabled', 'priority', 'starts_at', 'ends_at', 'description', 'metadata', 'is_default', 'auto_apply', 'min_order_amount', 'max_order_amount'];
    /**
     * Handle casts functionality with proper error handling.
     * @return array
     */
    protected function casts(): array
    {
        return ['is_enabled' => 'boolean', 'is_default' => 'boolean', 'auto_apply' => 'boolean', 'priority' => 'integer', 'starts_at' => 'datetime', 'ends_at' => 'datetime', 'min_order_amount' => 'decimal:2', 'max_order_amount' => 'decimal:2', 'metadata' => 'array'];
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
     * Handle zone functionality with proper error handling.
     * @return BelongsTo
     */
    public function zone(): BelongsTo
    {
        return $this->belongsTo(Zone::class);
    }
    /**
     * Handle items functionality with proper error handling.
     * @return HasMany
     */
    public function items(): HasMany
    {
        return $this->hasMany(PriceListItem::class);
    }
    /**
     * Handle customerGroups functionality with proper error handling.
     * @return BelongsToMany
     */
    public function customerGroups(): BelongsToMany
    {
        return $this->belongsToMany(CustomerGroup::class, 'group_price_list', 'price_list_id', 'group_id');
    }
    /**
     * Handle partners functionality with proper error handling.
     * @return BelongsToMany
     */
    public function partners(): BelongsToMany
    {
        return $this->belongsToMany(Partner::class, 'partner_price_list', 'price_list_id', 'partner_id');
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
     * Handle scopeByPriority functionality with proper error handling.
     * @param mixed $query
     * @param string $direction
     */
    public function scopeByPriority($query, string $direction = 'asc')
    {
        return $query->orderBy('priority', $direction);
    }
    /**
     * Handle scopeDefault functionality with proper error handling.
     * @param mixed $query
     */
    public function scopeDefault($query)
    {
        return $query->where('is_default', true);
    }
    /**
     * Handle scopeAutoApply functionality with proper error handling.
     * @param mixed $query
     */
    public function scopeAutoApply($query)
    {
        return $query->where('auto_apply', true);
    }
    /**
     * Handle scopeByCurrency functionality with proper error handling.
     * @param mixed $query
     * @param int $currencyId
     */
    public function scopeByCurrency($query, int $currencyId)
    {
        return $query->where('currency_id', $currencyId);
    }
    /**
     * Handle scopeByZone functionality with proper error handling.
     * @param mixed $query
     * @param int $zoneId
     */
    public function scopeByZone($query, int $zoneId)
    {
        return $query->where('zone_id', $zoneId);
    }
    /**
     * Handle scopeForOrderAmount functionality with proper error handling.
     * @param mixed $query
     * @param float $amount
     */
    public function scopeForOrderAmount($query, float $amount)
    {
        return $query->where(function ($q) use ($amount) {
            $q->whereNull('min_order_amount')->orWhere('min_order_amount', '<=', $amount);
        })->where(function ($q) use ($amount) {
            $q->whereNull('max_order_amount')->orWhere('max_order_amount', '>=', $amount);
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
     * Handle isDefault functionality with proper error handling.
     * @return bool
     */
    public function isDefault(): bool
    {
        return (bool) $this->is_default;
    }
    /**
     * Handle canAutoApply functionality with proper error handling.
     * @return bool
     */
    public function canAutoApply(): bool
    {
        return (bool) $this->auto_apply;
    }
    /**
     * Handle getEffectivePriceForProduct functionality with proper error handling.
     * @param Product $product
     * @return float|null
     */
    public function getEffectivePriceForProduct(Product $product): ?float
    {
        $item = $this->items()->where('product_id', $product->id)->first();
        return $item ? $item->net_amount : null;
    }
    /**
     * Handle getEffectivePriceForVariant functionality with proper error handling.
     * @param ProductVariant $variant
     * @return float|null
     */
    public function getEffectivePriceForVariant(ProductVariant $variant): ?float
    {
        $item = $this->items()->where('variant_id', $variant->id)->first();
        return $item ? $item->net_amount : null;
    }
    /**
     * Handle getItemsCountAttribute functionality with proper error handling.
     * @return int
     */
    public function getItemsCountAttribute(): int
    {
        return $this->items()->count();
    }
    /**
     * Handle getCustomerGroupsCountAttribute functionality with proper error handling.
     * @return int
     */
    public function getCustomerGroupsCountAttribute(): int
    {
        return $this->customerGroups()->count();
    }
    /**
     * Handle getPartnersCountAttribute functionality with proper error handling.
     * @return int
     */
    public function getPartnersCountAttribute(): int
    {
        return $this->partners()->count();
    }
    /**
     * Handle getActivitylogOptions functionality with proper error handling.
     * @return LogOptions
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logOnly(['name', 'code', 'currency_id', 'zone_id', 'is_enabled', 'priority', 'starts_at', 'ends_at'])->logOnlyDirty()->dontSubmitEmptyLogs();
    }
}