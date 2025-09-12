<?php declare(strict_types=1);

namespace App\Models\Translations;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class ZoneTranslation extends Model
{
    protected $table = 'zone_translations';

    protected $fillable = [
        'zone_id',
        'locale',
        'name',
        'description',
        'meta_title',
        'meta_description',
        'meta_keywords',
        'short_description',
        'long_description',
    ];

    protected function casts(): array
    {
        return [
            'meta_keywords' => 'array',
        ];
    }

    public $timestamps = true;

    public function zone(): BelongsTo
    {
        return $this->belongsTo(Zone::class);
    }
}


