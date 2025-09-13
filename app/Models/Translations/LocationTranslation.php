<?php

declare(strict_types=1);

namespace App\Models\Translations;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class LocationTranslation extends Model
{
    protected $table = 'location_translations';

    protected $fillable = [
        'location_id',
        'locale',
        'name',
        'slug',
        'description',
    ];

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }
}