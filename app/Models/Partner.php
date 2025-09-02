<?php declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

final class Partner extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'sh_partners';

    protected $fillable = [
        'name',
        'code',
        'tier_id',
        'contact_email',
        'contact_phone',
        'is_enabled',
        'discount_rate',
        'commission_rate',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'is_enabled' => 'boolean',
            'discount_rate' => 'decimal:4',
            'commission_rate' => 'decimal:4',
            'metadata' => 'array',
        ];
    }

    public function tier(): BelongsTo
    {
        return $this->belongsTo(PartnerTier::class);
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'sh_partner_users');
    }

    public function priceLists(): BelongsToMany
    {
        return $this->belongsToMany(PriceList::class, 'sh_partner_price_list', 'partner_id', 'price_list_id');
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function scopeEnabled($query)
    {
        return $query->where('is_enabled', true);
    }

    public function scopeByTier($query, int $tierId)
    {
        return $query->where('tier_id', $tierId);
    }

    public function getEffectiveDiscountRateAttribute(): float
    {
        return $this->discount_rate ?: ($this->tier->discount_rate ?? 0);
    }
}
