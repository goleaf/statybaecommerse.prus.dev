<?php

declare (strict_types=1);
namespace App\Models\Translations;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
/**
 * ProductTranslation
 * 
 * Eloquent model representing the ProductTranslation entity with comprehensive relationships, scopes, and business logic for the e-commerce system.
 * 
 * @property mixed $table
 * @property mixed $fillable
 * @property mixed $casts
 * @property mixed $timestamps
 * @method static \Illuminate\Database\Eloquent\Builder|ProductTranslation newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ProductTranslation newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ProductTranslation query()
 * @mixin \Eloquent
 */
final class ProductTranslation extends Model
{
    protected $table = 'product_translations';
    protected $fillable = ['product_id', 'locale', 'name', 'slug', 'summary', 'description', 'short_description', 'seo_title', 'seo_description', 'meta_keywords', 'alt_text'];
    protected $casts = ['product_id' => 'integer', 'meta_keywords' => 'array'];
    public $timestamps = true;
    /**
     * Handle product functionality with proper error handling.
     * @return BelongsTo
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Product::class);
    }
}