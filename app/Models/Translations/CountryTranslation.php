<?php

declare (strict_types=1);
namespace App\Models\Translations;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
/**
 * CountryTranslation
 * 
 * Eloquent model representing the CountryTranslation entity with comprehensive relationships, scopes, and business logic for the e-commerce system.
 * 
 * @property mixed $table
 * @property mixed $fillable
 * @property mixed $timestamps
 * @method static \Illuminate\Database\Eloquent\Builder|CountryTranslation newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CountryTranslation newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CountryTranslation query()
 * @mixin \Eloquent
 */
final class CountryTranslation extends Model
{
    use HasFactory;
    protected $table = 'country_translations';
    protected $fillable = ['country_id', 'locale', 'name', 'name_official', 'description'];
    /**
     * Handle casts functionality with proper error handling.
     * @return array
     */
    protected function casts(): array
    {
        return ['country_id' => 'integer'];
    }
    public $timestamps = true;
}