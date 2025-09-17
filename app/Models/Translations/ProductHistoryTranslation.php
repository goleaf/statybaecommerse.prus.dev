<?php

declare (strict_types=1);
namespace App\Models\Translations;

use Illuminate\Database\Eloquent\Model;
/**
 * ProductHistoryTranslation
 * 
 * Eloquent model representing the ProductHistoryTranslation entity with comprehensive relationships, scopes, and business logic for the e-commerce system.
 * 
 * @property mixed $fillable
 * @property mixed $casts
 * @property mixed $table
 * @property mixed $timestamps
 * @method static \Illuminate\Database\Eloquent\Builder|ProductHistoryTranslation newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ProductHistoryTranslation newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ProductHistoryTranslation query()
 * @mixin \Eloquent
 */
final class ProductHistoryTranslation extends Model
{
    protected $fillable = ['product_history_id', 'locale', 'action', 'description', 'field_name'];
    protected $casts = ['action' => 'string', 'description' => 'string', 'field_name' => 'string'];
    protected $table = 'product_history_translations';
    public $timestamps = false;
    /**
     * Handle productHistory functionality with proper error handling.
     */
    public function productHistory()
    {
        return $this->belongsTo(\App\Models\ProductHistory::class);
    }
}
