<?php declare(strict_types=1);

namespace App\Models\Translations;

use Database\Factories\BrandTranslationFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;

/**
 * BrandTranslation
 *
 * Eloquent model representing the BrandTranslation entity with comprehensive relationships, scopes, and business logic for the e-commerce system.
 *
 * @property mixed $table
 * @property mixed $fillable
 * @property mixed $timestamps
 *
 * @method static \Illuminate\Database\Eloquent\Builder|BrandTranslation newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|BrandTranslation newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|BrandTranslation query()
 *
 * @mixin \Eloquent
 */
final class BrandTranslation extends Model
{
    use HasFactory;

    protected $table = 'brand_translations';

    protected $fillable = ['brand_id', 'locale', 'name', 'slug', 'description', 'seo_title', 'seo_description'];

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory(): BrandTranslationFactory
    {
        return BrandTranslationFactory::new();
    }

    /**
     * Handle casts functionality with proper error handling.
     */
    protected function casts(): array
    {
        return ['brand_id' => 'integer'];
    }

    public $timestamps = true;

    /**
     * Handle brand functionality with proper error handling.
     */
    public function brand(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Brand::class);
    }
}
