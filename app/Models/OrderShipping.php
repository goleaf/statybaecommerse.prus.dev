<?php declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;

final class OrderShipping extends Model
{
    use HasFactory;

    protected $table = 'order_shippings';

    protected $fillable = [
        'order_id',
        'carrier',
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

    protected function casts(): array
    {
        return [
            'shipped_at' => 'datetime',
            'estimated_delivery' => 'datetime',
            'delivered_at' => 'datetime',
            'weight' => 'decimal:3',
            'cost' => 'decimal:2',
            'dimensions' => 'array',
            'metadata' => 'array',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function scopeShipped($query)
    {
        return $query->whereNotNull('shipped_at');
    }

    public function scopeDelivered($query)
    {
        return $query->whereNotNull('delivered_at');
    }

    public function scopeByCarrier($query, string $carrier)
    {
        return $query->where('carrier', $carrier);
    }

    public function isShipped(): bool
    {
        return !is_null($this->shipped_at);
    }

    public function isDelivered(): bool
    {
        return !is_null($this->delivered_at);
    }

    public function isInTransit(): bool
    {
        return $this->isShipped() && !$this->isDelivered();
    }

    public function getStatusAttribute(): string
    {
        return match (true) {
            $this->isDelivered() => 'delivered',
            $this->isShipped() => 'in_transit',
            default => 'pending'
        };
    }
}
