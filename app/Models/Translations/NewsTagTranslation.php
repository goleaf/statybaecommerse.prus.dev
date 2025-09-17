<?php

declare (strict_types=1);
namespace App\Models\Translations;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
/**
 * NewsTagTranslation
 * 
 * Eloquent model representing the NewsTagTranslation entity with comprehensive relationships, scopes, and business logic for the e-commerce system.
 * 
 * @property mixed $table
 * @property mixed $fillable
 * @method static \Illuminate\Database\Eloquent\Builder|NewsTagTranslation newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|NewsTagTranslation newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|NewsTagTranslation query()
 * @mixin \Eloquent
 */
final class NewsTagTranslation extends Model
{
    use HasFactory;
    protected $table = 'sh_news_tag_translations';
    protected $fillable = ['news_tag_id', 'locale', 'name', 'slug', 'description'];
    /**
     * Handle casts functionality with proper error handling.
     * @return array
     */
    protected function casts(): array
    {
        return ['news_tag_id' => 'integer'];
    }
}