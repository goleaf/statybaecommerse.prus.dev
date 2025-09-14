<?php

declare(strict_types=1);

namespace App\Models\Translations;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

final /**
 * NewsTranslation
 * 
 * Eloquent model representing a database entity with relationships and business logic.
 */
class NewsTranslation extends Model
{
    use HasFactory;

    protected $table = 'sh_news_translations';

    protected $fillable = [
        'news_id',
        'locale',
        'title',
        'slug',
        'summary',
        'content',
        'seo_title',
        'seo_description',
    ];

    protected function casts(): array
    {
        return [
            'news_id' => 'integer',
        ];
    }
}
