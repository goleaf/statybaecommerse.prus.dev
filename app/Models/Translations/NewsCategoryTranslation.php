<?php

declare(strict_types=1);

namespace App\Models\Translations;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

final /**
 * NewsCategoryTranslation
 * 
 * Eloquent model representing a database entity with relationships and business logic.
 */
class NewsCategoryTranslation extends Model
{
    use HasFactory;

    protected $table = 'sh_news_category_translations';

    protected $fillable = [
        'news_category_id',
        'locale',
        'name',
        'slug',
        'description',
    ];

    protected function casts(): array
    {
        return [
            'news_category_id' => 'integer',
        ];
    }
}
