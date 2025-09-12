<?php declare(strict_types=1);

namespace App\Models\Translations;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class BrandTranslation extends Model
{
    protected $table = 'brand_translations';
    
    protected $fillable = [
        'brand_id',
        'locale',
        'name',
        'slug',
        'description',
        'seo_title',
        'seo_description',
    ];

    protected function casts(): array
    {
        return [
            'brand_id' => 'integer',
        ];
    }

    public $timestamps = true;

    public function brand(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Brand::class);
    }
}
