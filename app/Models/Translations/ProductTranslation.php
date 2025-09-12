<?php declare(strict_types=1);

namespace App\Models\Translations;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class ProductTranslation extends Model
{
    protected $table = 'product_translations';
    
    protected $fillable = [
        'product_id',
        'locale',
        'name',
        'slug',
        'summary',
        'description',
        'short_description',
        'seo_title',
        'seo_description',
        'meta_keywords',
        'alt_text',
    ];

    protected $casts = [
        'product_id' => 'integer',
        'meta_keywords' => 'array',
    ];

    public $timestamps = true;

    public function product(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Product::class);
    }
}
