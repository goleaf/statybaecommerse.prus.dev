<?php

declare (strict_types=1);
namespace App\Models\Translations;

use Illuminate\Database\Eloquent\Model;
/**
 * DiscountConditionTranslation
 * 
 * Eloquent model representing the DiscountConditionTranslation entity with comprehensive relationships, scopes, and business logic for the e-commerce system.
 * 
 * @property mixed $table
 * @property mixed $fillable
 * @property mixed $timestamps
 * @method static \Illuminate\Database\Eloquent\Builder|DiscountConditionTranslation newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|DiscountConditionTranslation newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|DiscountConditionTranslation query()
 * @mixin \Eloquent
 */
final class DiscountConditionTranslation extends Model
{
    protected $table = 'discount_condition_translations';
    protected $fillable = ['discount_condition_id', 'locale', 'name', 'description', 'metadata'];
    /**
     * Handle casts functionality with proper error handling.
     * @return array
     */
    protected function casts(): array
    {
        return ['metadata' => 'array'];
    }
    public $timestamps = true;
}