<?php declare(strict_types=1);

namespace App\Models;

use App\Models\Scopes\ActiveScope;
use App\Models\Scopes\EnabledScope;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

/**
 * CustomerGroup
 *
 * Eloquent model representing the CustomerGroup entity with comprehensive relationships, scopes, and business logic for the e-commerce system.
 *
 * @property mixed $table
 * @property array $translatable
 * @property mixed $fillable
 * @method static \Illuminate\Database\Eloquent\Builder|CustomerGroup newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CustomerGroup newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CustomerGroup query()
 * @mixin \Eloquent
 */
#[ScopedBy([ActiveScope::class, EnabledScope::class])]
final class CustomerGroup extends Model
{
    use HasFactory, HasTranslations;

    protected $table = 'customer_groups';
    public array $translatable = ['name', 'description'];

    protected $fillable = [
        'name',
        'code',
        'description',
        'discount_percentage',
        'discount_fixed',
        'has_special_pricing',
        'has_volume_discounts',
        'can_view_prices',
        'can_place_orders',
        'can_view_catalog',
        'can_use_coupons',
        'is_active',
        'is_default',
        'sort_order',
        'type',
        'is_enabled',
        'conditions'
    ];

    /**
     * Handle casts functionality with proper error handling.
     * @return array
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
            'is_default' => 'boolean',
            'sort_order' => 'integer',
            'is_enabled' => 'boolean',
            'conditions' => 'array'
        ];
    }

    /**
     * Handle users functionality with proper error handling.
     * @return BelongsToMany
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'customer_group_user', 'customer_group_id', 'user_id')->withTimestamps();
    }

    /**
     * Handle customers functionality with proper error handling.
     * @return BelongsToMany
     */
    public function customers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'customer_group_user', 'customer_group_id', 'user_id')->withTimestamps();
    }

    /**
     * Handle discounts functionality with proper error handling.
     * @return BelongsToMany
     */
    public function discounts(): BelongsToMany
    {
        return $this->belongsToMany(Discount::class, 'discount_customer_groups');
    }

    /**
     * Handle priceLists functionality with proper error handling.
     * @return BelongsToMany
     */
    public function priceLists(): BelongsToMany
    {
        return $this->belongsToMany(PriceList::class, 'group_price_list', 'group_id', 'price_list_id');
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
     * Handle scopeWithDiscount functionality with proper error handling.
     * @param mixed $query
     */
    public function scopeWithDiscount($query)
    {
        return $query->where('discount_percentage', '>', 0);
    }

    /**
     * Handle getUsersCountAttribute functionality with proper error handling.
     * @return int
     */
    public function getUsersCountAttribute(): int
    {
        return $this->users()->count();
    }

    /**
     * Handle hasDiscountRate functionality with proper error handling.
     * @return bool
     */
    public function hasDiscountRate(): bool
    {
        return (float) $this->discount_percentage > 0;
    }

    /**
     * Handle getIsActiveAttribute functionality with proper error handling.
     * @return bool
     */
    public function getIsActiveAttribute(): bool
    {
        return (bool) $this->is_enabled;
    }

    /**
     * Handle setIsActiveAttribute functionality with proper error handling.
     * @param bool $value
     * @return void
     */
    public function setIsActiveAttribute(bool $value): void
    {
        $this->attributes['is_enabled'] = $value;
    }
}
