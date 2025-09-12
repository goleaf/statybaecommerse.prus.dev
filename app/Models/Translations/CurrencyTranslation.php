<?php

declare(strict_types=1);

namespace App\Models\Translations;

use Illuminate\Database\Eloquent\Model;

final class CurrencyTranslation extends Model
{
    protected $table = 'currency_translations';

    protected $guarded = [];

    public $timestamps = true;
}
