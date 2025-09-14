<?php

declare (strict_types=1);
namespace App\Models\Translations;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
/**
 * PriceListItemTranslation
 * 
 * Eloquent model representing the PriceListItemTranslation entity with comprehensive relationships, scopes, and business logic for the e-commerce system.
 * 
 * @property mixed $table
 * @property mixed $fillable
 * @property mixed $casts
 * @property mixed $timestamps
 * @method static \Illuminate\Database\Eloquent\Builder|PriceListItemTranslation newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PriceListItemTranslation newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PriceListItemTranslation query()
 * @mixin \Eloquent
 */
final class PriceListItemTranslation extends Model
{
    protected $table = 'price_list_item_translations';
    protected $fillable = ['price_list_item_id', 'locale', 'name', 'description', 'notes'];
    protected $casts = ['price_list_item_id' => 'integer'];
    public $timestamps = true;
    /**
     * Handle priceListItem functionality with proper error handling.
     * @return BelongsTo
     */
    public function priceListItem(): BelongsTo
    {
        return $this->belongsTo(\App\Models\PriceListItem::class);
    }
}