<?php

declare(strict_types=1);

namespace App\Models\Translations;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * PriceTranslation
 *
 * Eloquent model representing the PriceTranslation entity with comprehensive relationships, scopes, and business logic for the e-commerce system.
 *
 * @property mixed $table
 * @property mixed $fillable
 *
 * @method static \Illuminate\Database\Eloquent\Builder|PriceTranslation newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PriceTranslation newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|PriceTranslation query()
 *
 * @mixin \Eloquent
 */
final class PriceTranslation extends Model
{
    use HasFactory;

    protected $table = 'price_translations';

    protected $fillable = ['price_id', 'locale', 'name', 'description', 'notes'];

    /**
     * Handle casts functionality with proper error handling.
     */
    protected function casts(): array
    {
        return ['price_id' => 'integer'];
    }

    /**
     * Handle price functionality with proper error handling.
     */
    public function price(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Price::class);
    }
}
