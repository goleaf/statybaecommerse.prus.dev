<?php

declare(strict_types=1);

namespace App\Models\Translations;

use App\Support\Html\HtmlSanitizer;
use Database\Factories\ProductTranslationFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * ProductTranslation
 *
 * Eloquent model representing the ProductTranslation entity with comprehensive relationships, scopes, and business logic for the e-commerce system.
 *
 * @property mixed $table
 * @property mixed $fillable
 * @property mixed $casts
 * @property mixed $timestamps
 *
 * @method static \Illuminate\Database\Eloquent\Builder|ProductTranslation newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ProductTranslation newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ProductTranslation query()
 *
 * @mixin \Eloquent
 */
final class ProductTranslation extends Model
{
    use HasFactory;

    protected $table = 'product_translations';

    protected $fillable = ['product_id', 'locale', 'name', 'slug', 'summary', 'description', 'short_description', 'seo_title', 'seo_description', 'meta_keywords', 'alt_text'];

    protected $casts = ['product_id' => 'integer', 'meta_keywords' => 'array'];

    public $timestamps = true;

    protected static function booted(): void
    {
        self::saving(static function (ProductTranslation $translation): void {
            /** @var HtmlSanitizer $sanitizer */
            $sanitizer = app(HtmlSanitizer::class);

            foreach (['description', 'short_description', 'summary'] as $field) {
                $value = $translation->{$field};

                if (! is_string($value) || trim($value) === '') {
                    continue;
                }

                // Align translation payloads with the shared sanitizer policy.
                $translation->{$field} = $sanitizer->sanitize($value);
            }
        });
    }

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory(): ProductTranslationFactory
    {
        return ProductTranslationFactory::new();
    }

    /**
     * Handle product functionality with proper error handling.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Product::class);
    }

    protected static function booted(): void
    {
        static::saving(static function (ProductTranslation $translation): void {
            /** @var HtmlSanitizer $sanitizer */
            $sanitizer = app(HtmlSanitizer::class);

            foreach (['description', 'short_description', 'summary'] as $field) {
                $value = $translation->{$field};

                if (! is_string($value) || trim($value) === '') {
                    continue;
                }

                // Align translation payloads with the shared sanitizer policy.
                $translation->{$field} = $sanitizer->sanitize($value);
            }
        });
    }
}
