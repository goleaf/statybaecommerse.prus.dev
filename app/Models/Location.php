<?php declare(strict_types=1);

namespace App\Models;

use App\Traits\HasTranslations;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Translatable\HasTranslations as SpatieHasTranslations;

final class Location extends Model
{
    use HasFactory, SoftDeletes, SpatieHasTranslations;

    protected $table = 'locations';

    protected $fillable = [
        'name',
        'slug',
        'description',
        'code',
        'address_line_1',
        'address_line_2',
        'city',
        'state',
        'postal_code',
        'country_code',
        'phone',
        'email',
        'is_enabled',
        'is_default',
        'type',
    ];

    public array $translatable = ['name', 'slug', 'description'];

    protected function casts(): array
    {
        return [
            'is_enabled' => 'boolean',
            'is_default' => 'boolean',
        ];
    }

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class, 'country_code', 'code');
    }

    public function inventories(): HasMany
    {
        return $this->hasMany(Inventory::class);
    }

    public function variantInventories(): HasMany
    {
        return $this->hasMany(VariantInventory::class);
    }

    public function scopeEnabled($query)
    {
        return $query->where('is_enabled', true);
    }

    public function scopeDefault($query)
    {
        return $query->where('is_default', true);
    }

    public function scopeByType($query, string $type)
    {
        return $query->where('type', $type);
    }

    public function getFullAddressAttribute(): string
    {
        $parts = array_filter([
            $this->address_line_1,
            $this->address_line_2,
            $this->city,
            $this->state,
            $this->postal_code,
        ]);

        return implode(', ', $parts);
    }
}
