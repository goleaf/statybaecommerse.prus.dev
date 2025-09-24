<?php

declare(strict_types=1);

namespace App\Models\Translations;

use Illuminate\Database\Eloquent\Model;

/**
 * CategoryTranslation
 *
 * Eloquent model representing the CategoryTranslation entity with comprehensive relationships, scopes, and business logic for the e-commerce system.
 *
 * @property mixed $table
 * @property mixed $fillable
 * @property mixed $casts
 * @property mixed $timestamps
 *
 * @method static \Illuminate\Database\Eloquent\Builder|CategoryTranslation newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CategoryTranslation newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CategoryTranslation query()
 *
 * @mixin \Eloquent
 */
final class CategoryTranslation extends Model
{
    protected $table = 'category_translations';

    protected $fillable = ['category_id', 'locale', 'name', 'slug', 'description', 'seo_title', 'seo_description'];

    protected $casts = ['category_id' => 'integer'];

    public $timestamps = true;
}
