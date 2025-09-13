<?php

declare(strict_types=1);

namespace App\Models\Translations;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class ReviewTranslation extends Model
{
    protected $table = 'review_translations';

    protected $fillable = [
        'review_id',
        'locale',
        'title',
        'comment',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'metadata' => 'array',
        ];
    }

    public function review(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Review::class);
    }
}
