<?php declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Translatable\HasTranslations;

final class Currency extends Model
{
    use HasFactory, SoftDeletes, HasTranslations;

    protected $table = 'currencies';

    protected $fillable = [
        'name',
        'code',
        'symbol',
        'exchange_rate',
        'is_default',
        'is_enabled',
        'decimal_places',
    ];

    public array $translatable = ['name'];

    protected function casts(): array
    {
        return [
            'exchange_rate' => 'decimal:6',
            'is_default' => 'boolean',
            'is_enabled' => 'boolean',
            'decimal_places' => 'integer',
        ];
    }

    public function zones(): HasMany
    {
        return $this->hasMany(Zone::class);
    }

    public function prices(): HasMany
    {
        return $this->hasMany(Price::class);
    }

    public function scopeEnabled($query)
    {
        return $query->where('is_enabled', true);
    }

    public function scopeDefault($query)
    {
        return $query->where('is_default', true);
    }

    public function getFormattedSymbolAttribute(): string
    {
        return $this->symbol ?? $this->code;
    }
}
