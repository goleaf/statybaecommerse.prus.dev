<?php declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

final class Country extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'sh_countries';

    protected $fillable = [
        'name',
        'code',
        'iso_code',
        'phone_code',
        'is_enabled',
    ];

    protected function casts(): array
    {
        return [
            'is_enabled' => 'boolean',
        ];
    }

    public function zones(): BelongsToMany
    {
        return $this->belongsToMany(Zone::class, 'sh_country_zone', 'country_id', 'zone_id');
    }

    public function addresses(): HasMany
    {
        return $this->hasMany(Address::class, 'country_code', 'code');
    }

    public function scopeEnabled($query)
    {
        return $query->where('is_enabled', true);
    }

    public function getDisplayNameAttribute(): string
    {
        return $this->phone_code ? "{$this->name} (+{$this->phone_code})" : $this->name;
    }
}
