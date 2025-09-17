<?php

declare (strict_types=1);
namespace App\Models\Translations;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
/**
 * NewsCategoryTranslation
 * 
 * Eloquent model representing the NewsCategoryTranslation entity with comprehensive relationships, scopes, and business logic for the e-commerce system.
 * 
 * @property mixed $table
 * @property mixed $fillable
 * @method static \Illuminate\Database\Eloquent\Builder|NewsCategoryTranslation newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|NewsCategoryTranslation newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|NewsCategoryTranslation query()
 * @mixin \Eloquent
 */
final class NewsCategoryTranslation extends Model
{
    use HasFactory;
    protected $table = 'sh_news_category_translations';
    protected $fillable = ['news_category_id', 'locale', 'name', 'slug', 'description'];
    /**
     * Handle casts functionality with proper error handling.
     * @return array
     */
    protected function casts(): array
    {
        return ['news_category_id' => 'integer'];
    }
}