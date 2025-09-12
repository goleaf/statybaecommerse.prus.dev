<?php

declare(strict_types=1);

namespace App\Models\Translations;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

final class NewsTranslation extends Model
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
}
