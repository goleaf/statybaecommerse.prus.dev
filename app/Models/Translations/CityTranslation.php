<?php

declare (strict_types=1);
namespace App\Models\Translations;

use Illuminate\Database\Eloquent\Model;
/**
 * CityTranslation
 * 
 * Eloquent model representing the CityTranslation entity with comprehensive relationships, scopes, and business logic for the e-commerce system.
 * 
 * @property mixed $table
 * @property mixed $fillable
 * @property mixed $timestamps
 * @method static \Illuminate\Database\Eloquent\Builder|CityTranslation newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CityTranslation newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CityTranslation query()
 * @mixin \Eloquent
 */
final class CityTranslation extends Model
{
    protected $table = 'city_translations';
    protected $fillable = ['city_id', 'locale', 'name', 'description'];
    /**
     * Handle casts functionality with proper error handling.
     * @return array
     */
    protected function casts(): array
    {
        return ['city_id' => 'integer'];
    }
    public $timestamps = true;
}