<?php

declare(strict_types=1);

namespace App\Models\Translations;

use Illuminate\Database\Eloquent\Model;

final class DiscountConditionTranslation extends Model
{
    protected $table = 'discount_condition_translations';

    protected $fillable = [
        'discount_condition_id',
        'locale',
        'name',
        'description',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'metadata' => 'array',
        ];
    }

    public $timestamps = true;
}
