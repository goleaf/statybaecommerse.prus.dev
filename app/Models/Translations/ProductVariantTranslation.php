<?php

declare(strict_types=1);

namespace App\Models\Translations;

use App\Models\ProductVariant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class ProductVariantTranslation extends Model
{
    use HasFactory;

    protected $table = 'product_variant_translations';

    protected $fillable = [
        'product_variant_id',
        'locale',
        'name',
        'description',
        'seo_title',
        'seo_description',
    ];

    protected $casts = [
        'product_variant_id' => 'int',
    ];

    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }
}

