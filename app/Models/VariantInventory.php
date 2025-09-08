<?php declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;

final class VariantInventory extends Model
{
    use HasFactory;

    protected $table = 'variant_inventories';

    protected $fillable = [
        'variant_id',
        'location_id',
        'stock',
        'reserved',
        'incoming',
        'threshold',
        'is_tracked',
    ];

    protected function casts(): array
    {
        return [
            'stock' => 'integer',
            'reserved' => 'integer',
            'incoming' => 'integer',
            'threshold' => 'integer',
            'is_tracked' => 'boolean',
        ];
    }

    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class);
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
        return $query->whereRaw('stock <= threshold');
    }

    public function scopeOutOfStock($query)
    {
        return $query->where('stock', '<=', 0);
    }

    public function getAvailableStockAttribute(): int
    {
        return max(0, $this->stock - $this->reserved);
    }

    public function isLowStock(): bool
    {
        return $this->stock <= $this->threshold;
    }

    public function isOutOfStock(): bool
    {
        return $this->available_stock <= 0;
    }
}
