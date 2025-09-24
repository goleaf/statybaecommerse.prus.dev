<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Scopes\ActiveScope;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * ProductImage
 *
 * Eloquent model representing the ProductImage entity with comprehensive relationships, scopes, and business logic for the e-commerce system.
 *
 * @property mixed $table
 * @property mixed $fillable
 *
 * @method static \Illuminate\Database\Eloquent\Builder|ProductImage newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ProductImage newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ProductImage query()
 *
 * @mixin \Eloquent
 */
#[ScopedBy([ActiveScope::class])]
final class ProductImage extends Model
{
    use HasFactory;

    protected $table = 'product_images';

    protected $fillable = ['product_id', 'path', 'alt_text', 'sort_order'];

    /**
     * Handle product functionality with proper error handling.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
