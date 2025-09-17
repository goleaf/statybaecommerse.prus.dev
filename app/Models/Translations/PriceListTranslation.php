<?php

declare (strict_types=1);
namespace App\Models\Translations;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
/**
 * PriceListTranslation
 * 
 * Eloquent model representing the PriceListTranslation entity with comprehensive relationships, scopes, and business logic for the e-commerce system.
 * 
 * @property mixed $table
 * @property mixed $fillable
 * @property mixed $timestamps
 * @method static \Illuminate\Database\Eloquent\Builder|PriceListTranslation newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PriceListTranslation newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PriceListTranslation query()
 * @mixin \Eloquent
 */
final class PriceListTranslation extends Model
{
    protected $table = 'price_list_translations';
    protected $fillable = ['price_list_id', 'locale', 'name', 'description', 'meta_title', 'meta_description', 'meta_keywords'];
    /**
     * Handle casts functionality with proper error handling.
     * @return array
     */
    protected function casts(): array
    {
        return ['price_list_id' => 'integer', 'meta_keywords' => 'array'];
    }
    public $timestamps = true;
    /**
     * Handle priceList functionality with proper error handling.
     * @return BelongsTo
     */
    public function priceList(): BelongsTo
    {
        return $this->belongsTo(\App\Models\PriceList::class);
    }
}