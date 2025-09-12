<?php

declare(strict_types=1);

namespace App\Models\Translations;

use Illuminate\Database\Eloquent\Model;

final class CategoryTranslation extends Model
{
    protected $table = 'category_translations';

    protected $fillable = [
        'category_id',
        'locale',
        'name',
        'description',
        'short_description',
        'seo_title',
        'seo_description',
        'seo_keywords',
    ];

    protected $casts = [
        'category_id' => 'integer',
    ];

    public $timestamps = true;
}
