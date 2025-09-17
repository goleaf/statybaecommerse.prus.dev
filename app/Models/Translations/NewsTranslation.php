<?php

declare (strict_types=1);
namespace App\Models\Translations;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
/**
 * NewsTranslation
 * 
 * Eloquent model representing the NewsTranslation entity with comprehensive relationships, scopes, and business logic for the e-commerce system.
 * 
 * @property mixed $table
 * @property mixed $fillable
 * @method static \Illuminate\Database\Eloquent\Builder|NewsTranslation newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|NewsTranslation newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|NewsTranslation query()
 * @mixin \Eloquent
 */
final class NewsTranslation extends Model
{
    use HasFactory;
    protected $table = 'sh_news_translations';
    protected $fillable = ['news_id', 'locale', 'title', 'slug', 'summary', 'content', 'seo_title', 'seo_description'];
    /**
     * Handle casts functionality with proper error handling.
     * @return array
     */
    protected function casts(): array
    {
        return ['news_id' => 'integer'];
    }
}