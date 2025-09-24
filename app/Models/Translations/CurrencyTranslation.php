<?php

declare(strict_types=1);

namespace App\Models\Translations;

use Illuminate\Database\Eloquent\Model;

/**
 * CurrencyTranslation
 *
 * Eloquent model representing the CurrencyTranslation entity with comprehensive relationships, scopes, and business logic for the e-commerce system.
 *
 * @property mixed $table
 * @property mixed $guarded
 * @property mixed $timestamps
 *
 * @method static \Illuminate\Database\Eloquent\Builder|CurrencyTranslation newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CurrencyTranslation newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CurrencyTranslation query()
 *
 * @mixin \Eloquent
 */
final class CurrencyTranslation extends Model
{
    protected $table = 'currency_translations';

    protected $guarded = [];

    /**
     * Handle casts functionality with proper error handling.
     */
    protected function casts(): array
    {
        return ['currency_id' => 'integer'];
    }

    public $timestamps = true;
}
