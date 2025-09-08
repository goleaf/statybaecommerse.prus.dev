<?php declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;

final class PriceListItem extends Model
{
    use HasFactory;

    protected $table = 'price_list_items';

    protected $fillable = [
        'price_list_id',
        'product_id',
        'variant_id',
        'net_amount',
        'compare_amount',
    ];

    protected function casts(): array
    {
        return [
            'net_amount' => 'decimal:4',
            'compare_amount' => 'decimal:4',
        ];
    }

    public function priceList(): BelongsTo
    {
        return $this->belongsTo(PriceList::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class);
    }

    public function getDiscountPercentageAttribute(): ?int
    {
        if (!$this->compare_amount || $this->compare_amount <= $this->net_amount) {
            return null;
        }

        return (int) round((($this->compare_amount - $this->net_amount) / $this->compare_amount) * 100);
    }
}
