<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Translations\PriceListTranslation;
use App\Traits\HasTranslations;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

final /**
 * PriceList
 * 
 * Eloquent model representing a database entity with relationships and business logic.
 */
class PriceList extends Model
{
    use HasFactory, HasTranslations, LogsActivity, SoftDeletes;

    protected string $translationModel = PriceListTranslation::class;

    protected $table = 'price_lists';

    protected $fillable = [
        'name',
        'code',
        'currency_id',
        'zone_id',
        'is_enabled',
        'priority',
        'starts_at',
        'ends_at',
        'description',
        'metadata',
        'is_default',
        'auto_apply',
        'min_order_amount',
        'max_order_amount',
    ];

    protected function casts(): array
    {
        return [
            'is_enabled' => 'boolean',
            'is_default' => 'boolean',
            'auto_apply' => 'boolean',
            'priority' => 'integer',
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'min_order_amount' => 'decimal:2',
            'max_order_amount' => 'decimal:2',
            'metadata' => 'array',
        ];
    }

    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class);
    }

    public function zone(): BelongsTo
    {
        return $this->belongsTo(Zone::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(PriceListItem::class);
    }

    public function customerGroups(): BelongsToMany
    {
        return $this->belongsToMany(CustomerGroup::class, 'group_price_list', 'price_list_id', 'group_id');
    }

    public function partners(): BelongsToMany
    {
        return $this->belongsToMany(Partner::class, 'partner_price_list', 'price_list_id', 'partner_id');
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

    public function scopeByPriority($query, string $direction = 'asc')
    {
        return $query->orderBy('priority', $direction);
    }

    public function scopeDefault($query)
    {
        return $query->where('is_default', true);
    }

    public function scopeAutoApply($query)
    {
        return $query->where('auto_apply', true);
    }

    public function scopeByCurrency($query, int $currencyId)
    {
        return $query->where('currency_id', $currencyId);
    }

    public function scopeByZone($query, int $zoneId)
    {
        return $query->where('zone_id', $zoneId);
    }

    public function scopeForOrderAmount($query, float $amount)
    {
        return $query->where(function ($q) use ($amount) {
            $q->whereNull('min_order_amount')
                ->orWhere('min_order_amount', '<=', $amount);
        })->where(function ($q) use ($amount) {
            $q->whereNull('max_order_amount')
                ->orWhere('max_order_amount', '>=', $amount);
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

    public function isDefault(): bool
    {
        return (bool) $this->is_default;
    }

    public function canAutoApply(): bool
    {
        return (bool) $this->auto_apply;
    }

    public function getEffectivePriceForProduct(Product $product): ?float
    {
        $item = $this->items()->where('product_id', $product->id)->first();

        return $item ? $item->net_amount : null;
    }

    public function getEffectivePriceForVariant(ProductVariant $variant): ?float
    {
        $item = $this->items()->where('variant_id', $variant->id)->first();

        return $item ? $item->net_amount : null;
    }

    public function getItemsCountAttribute(): int
    {
        return $this->items()->count();
    }

    public function getCustomerGroupsCountAttribute(): int
    {
        return $this->customerGroups()->count();
    }

    public function getPartnersCountAttribute(): int
    {
        return $this->partners()->count();
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'code', 'currency_id', 'zone_id', 'is_enabled', 'priority', 'starts_at', 'ends_at'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }
}
