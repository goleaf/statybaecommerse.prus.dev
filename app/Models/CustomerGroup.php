<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Schema;
use Spatie\Translatable\HasTranslations;

/**
 * CustomerGroup
 *
 * Eloquent model representing the CustomerGroup entity with comprehensive relationships, scopes, and business logic for the e-commerce system.
 *
 * @property mixed $table
 * @property array $translatable
 * @property mixed $fillable
 *
 * @method static \Illuminate\Database\Eloquent\Builder|CustomerGroup newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CustomerGroup newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CustomerGroup query()
 *
 * @mixin \Eloquent
 */
final class CustomerGroup extends Model
{
    use HasFactory;
    use HasTranslations;
    use SoftDeletes;

    protected $table = 'customer_groups';

    public array $translatable = ['name', 'description'];

    /**
     * Guard the attributes that can be mass-assigned so factories and admin forms stay in sync.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'code',
        'color',
        'icon',
        'description',
        'slug',
        'discount_percentage',
        'discount_fixed',
        'has_special_pricing',
        'has_volume_discounts',
        'can_view_prices',
        'can_place_orders',
        'can_view_catalog',
        'can_use_coupons',
        'is_active',
        'is_enabled',
        'is_default',
        'sort_order',
        'type',
        'metadata',
        'conditions',
    ];

    /**
     * Handle casts functionality with proper error handling.
     */
    protected function casts(): array
    {
        return [
            'discount_percentage' => 'decimal:2',
            'discount_fixed' => 'decimal:2',
            'has_special_pricing' => 'boolean',
            'has_volume_discounts' => 'boolean',
            'can_view_prices' => 'boolean',
            'can_place_orders' => 'boolean',
            'can_view_catalog' => 'boolean',
            'can_use_coupons' => 'boolean',
            'is_active' => 'boolean',
            'is_enabled' => 'boolean',
            'is_default' => 'boolean',
            'sort_order' => 'integer',
            'type' => 'string',
            'metadata' => 'array',
            'conditions' => 'array',
            'deleted_at' => 'datetime',
        ];
    }

    /**
     * Handle users functionality with proper error handling.
     */
    public function users(): BelongsToMany
    {
        // Maintain the pivot mapping used by the Filament resource and the seed data.
        return $this->belongsToMany(User::class, 'customer_group_user', 'customer_group_id', 'user_id')->withTimestamps();
    }

    /**
     * Handle customers functionality with proper error handling.
     */
    public function customers(): BelongsToMany
    {
        // Keep a dedicated accessor for customer assignments while gracefully falling back when the pivot table is absent.
        $pivotTable = Schema::hasTable('customer_group_customer') ? 'customer_group_customer' : 'customer_group_user';
        $relatedKey = $pivotTable === 'customer_group_customer' ? 'customer_id' : 'user_id';

        return $this->belongsToMany(Customer::class, $pivotTable, 'customer_group_id', $relatedKey)->withTimestamps();
    }

    /**
     * Handle discounts functionality with proper error handling.
     */
    public function discounts(): BelongsToMany
    {
        return $this->belongsToMany(Discount::class, 'discount_customer_groups');
    }

    /**
     * Handle priceLists functionality with proper error handling.
     */
    public function priceLists(): BelongsToMany
    {
        // Reference the explicit pivot so B2B price list syncs remain accurate.
        return $this->belongsToMany(PriceList::class, 'group_price_list', 'group_id', 'price_list_id');
    }

    /**
     * Scope a query to only include enabled groups.
     */
    public function scopeEnabled(Builder $query): Builder
    {
        return $query->where('is_enabled', true);
    }

    /**
     * Scope a query to only include groups with a positive discount rate.
     */
    public function scopeWithDiscount(Builder $query): Builder
    {
        return $query->where('discount_percentage', '>', 0);
    }

    /**
     * Handle getUsersCountAttribute functionality with proper error handling.
     */
    public function getUsersCountAttribute(): int
    {
        return $this->users()->count();
    }

    /**
     * Handle hasDiscountRate functionality with proper error handling.
     */
    public function hasDiscountRate(): bool
    {
        return (float) $this->discount_percentage > 0;
    }

    /**
     * Handle getIsActiveAttribute functionality with proper error handling.
     */
    public function getIsActiveAttribute(): bool
    {
        // Mirror both legacy (is_enabled) and current (is_active) columns so toggles stay in sync.
        return (bool) ($this->attributes['is_active'] ?? $this->attributes['is_enabled'] ?? false);
    }

    /**
     * Handle setIsActiveAttribute functionality with proper error handling.
     */
    public function setIsActiveAttribute(bool $value): void
    {
        // Persist the new state to both flags to remain backward compatible with historical migrations.
        $this->attributes['is_active'] = $value;
        $this->attributes['is_enabled'] = $value;
    }

    /**
     * Get metadata field value
     *
     * @param  mixed $default
     * @return mixed
     */
    public function getMetadata(string $key, $default = null)
    {
        // Use Arr::get so nested metadata keys can be fetched without additional helpers.
        return Arr::get($this->metadata ?? [], $key, $default);
    }

    /**
     * Set metadata field value
     *
     * @param mixed $value
     */
    public function setMetadata(string $key, $value): void
    {
        // Merge the new key into the existing array while keeping JSON casting intact.
        $metadata = $this->metadata ?? [];
        Arr::set($metadata, $key, $value);
        $this->metadata = $metadata;
    }
}
