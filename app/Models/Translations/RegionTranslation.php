<?php declare(strict_types=1);

namespace App\Models\Translations;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class RegionTranslation extends Model
{
    use HasFactory;

    protected $table = 'region_translations';

    protected $fillable = [
        'region_id',
        'locale',
        'name',
        'description',
    ];

    public $timestamps = true;

    public function region(): BelongsTo
    {
        return $this->belongsTo(Region::class);
    }
}


