<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Scopes\ActiveScope;
use App\Models\Scopes\EnabledScope;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
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
        'slug',
        'discount_percentage',
        'is_enabled',
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
            'is_enabled' => 'boolean',
            'metadata' => 'array',
            'conditions' => 'array',
        ];
    }

    /**
     * Handle users functionality with proper error handling.
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'customer_group_user', 'customer_group_id', 'user_id')->withTimestamps();
    }

    /**
     * Handle customers functionality with proper error handling.
     */
    public function customers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'customer_group_user', 'customer_group_id', 'user_id')->withTimestamps();
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
        return $this->belongsToMany(PriceList::class, 'group_price_list', 'group_id', 'price_list_id');
    }

    /**
     * Handle scopeEnabled functionality with proper error handling.
     *
     * @param  mixed  $query
     */
    public function scopeEnabled($query)
    {
        return $query->where('is_enabled', true);
    }

    /**
     * Handle scopeWithDiscount functionality with proper error handling.
     *
     * @param  mixed  $query
     */
    public function scopeWithDiscount($query)
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
        return (bool) $this->is_enabled;
    }

    /**
     * Handle setIsActiveAttribute functionality with proper error handling.
     */
    public function setIsActiveAttribute(bool $value): void
    {
        $this->attributes['is_enabled'] = $value;
    }

    /**
     * Get metadata field value
     *
     * @param  mixed  $default
     * @return mixed
     */
    public function getMetadata(string $key, $default = null)
    {
        return $this->metadata[$key] ?? $default;
    }

    /**
     * Set metadata field value
     *
     * @param  mixed  $value
     */
    public function setMetadata(string $key, $value): void
    {
        $metadata = $this->metadata ?? [];
        $metadata[$key] = $value;
        $this->metadata = $metadata;
    }
}
