<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Scopes\UserOwnedScope;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * OrderShipping
 *
 * Eloquent model representing the OrderShipping entity with comprehensive relationships, scopes, and business logic for the e-commerce system.
 *
 * @property mixed $table
 * @property mixed $fillable
 *
 * @method static \Illuminate\Database\Eloquent\Builder|OrderShipping newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|OrderShipping newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|OrderShipping query()
 *
 * @mixin \Eloquent
 */
#[ScopedBy([UserOwnedScope::class])]
final class OrderShipping extends Model
{
    use HasFactory;

    protected $table = 'order_shippings';

    protected $fillable = [
        'order_id',
        'carrier_name',
        'service',
        'tracking_number',
        'tracking_url',
        'shipped_at',
        'estimated_delivery',
        'delivered_at',
        'weight',
        'dimensions',
        'cost',
        'metadata',
    ];

    /**
     * Handle casts functionality with proper error handling.
     */
    protected function casts(): array
    {
        return [
            'shipped_at' => 'datetime',
            'estimated_delivery' => 'datetime',
            'delivered_at' => 'datetime',
            'weight' => 'decimal:3',
            'base_cost' => 'decimal:2',
            'insurance_cost' => 'decimal:2',
            'total_cost' => 'decimal:2',
            'dimensions' => 'array',
            'metadata' => 'array',
            'is_delivered' => 'boolean',
        ];
    }

    /**
     * Handle order functionality with proper error handling.
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Handle scopeShipped functionality with proper error handling.
     *
     * @param  mixed  $query
     */
    public function scopeShipped($query)
    {
        return $query->whereNotNull('shipped_at');
    }

    /**
     * Handle scopeDelivered functionality with proper error handling.
     *
     * @param  mixed  $query
     */
    public function scopeDelivered($query)
    {
        return $query->whereNotNull('delivered_at');
    }

    /**
     * Handle scopeByCarrier functionality with proper error handling.
     *
     * @param  mixed  $query
     */
    public function scopeByCarrier($query, string $carrier)
    {
        return $query->where('carrier_name', $carrier);
    }

    /**
     * Handle isShipped functionality with proper error handling.
     */
    public function isShipped(): bool
    {
        return ! is_null($this->shipped_at);
    }

    /**
     * Handle isDelivered functionality with proper error handling.
     */
    public function isDelivered(): bool
    {
        return ! is_null($this->delivered_at);
    }

    /**
     * Handle isInTransit functionality with proper error handling.
     */
    public function isInTransit(): bool
    {
        return $this->isShipped() && ! $this->isDelivered();
    }

    /**
     * Handle getStatusAttribute functionality with proper error handling.
     */
    public function getStatusAttribute(): string
    {
        return match (true) {
            $this->isDelivered() => 'delivered',
            $this->isShipped() => 'in_transit',
            default => 'pending',
        };
    }
}
