<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class Inventory extends Model
{
    use HasFactory;

    protected $table = 'inventories';

    protected $fillable = [
        'product_id',
        'location_id',
        'quantity',
        'reserved',
        'incoming',
        'threshold',
        'is_tracked',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
            'reserved' => 'integer',
            'incoming' => 'integer',
            'threshold' => 'integer',
            'is_tracked' => 'boolean',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    public function scopeTracked($query)
    {
        return $query->where('is_tracked', true);
    }

    public function scopeLowStock($query)
    {
        return $query->whereRaw('quantity <= threshold');
    }

    public function getAvailableQuantityAttribute(): int
    {
        return max(0, $this->quantity - $this->reserved);
    }

    public function isLowStock(): bool
    {
        return $this->quantity <= $this->threshold;
    }

    public function isOutOfStock(): bool
    {
        return $this->available_quantity <= 0;
    }
}
