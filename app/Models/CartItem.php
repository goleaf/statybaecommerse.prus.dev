<?php declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

final class CartItem extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'session_id',
        'user_id',
        'product_id',
        'variant_id',
        'quantity',
        'unit_price',
        'total_price',
        'product_snapshot',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'unit_price' => 'decimal:2',
        'total_price' => 'decimal:2',
        'product_snapshot' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'variant_id');
    }

    public function updateTotalPrice(): void
    {
        $this->total_price = $this->unit_price * $this->quantity;
        $this->save();
    }

    public function getFormattedTotalPriceAttribute(): string
    {
        return app_money_format($this->total_price);
    }

    public function getFormattedUnitPriceAttribute(): string
    {
        return app_money_format($this->unit_price);
    }

    public function scopeForSession($query, string $sessionId)
    {
        return $query->where('session_id', $sessionId);
    }

    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }
}