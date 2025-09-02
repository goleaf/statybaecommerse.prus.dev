<?php declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

final class Partner extends Model implements HasMedia
{
    use HasFactory, SoftDeletes;
    use InteractsWithMedia;

    protected $table = 'partners';

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
        return $this->belongsToMany(User::class, 'partner_users');
    }

    public function priceLists(): BelongsToMany
    {
        return $this->belongsToMany(PriceList::class, 'partner_price_list', 'partner_id', 'price_list_id');
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

    public function getLogoUrl(?string $size = null): ?string
    {
        if (!$size) {
            return $this->getFirstMediaUrl('logo') ?: null;
        }

        return $this->getFirstMediaUrl('logo', "logo-{$size}") ?: $this->getFirstMediaUrl('logo');
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('logo')
            ->singleFile()
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/svg+xml']);
    }

    public function registerMediaConversions(Media $media = null): void
    {
        // Logo conversions with multiple resolutions
        $this
            ->addMediaConversion('logo-xs')
            ->performOnCollections('logo')
            ->width(64)
            ->height(64)
            ->format('webp')
            ->quality(85)
            ->sharpen(10)
            ->optimize();

        $this
            ->addMediaConversion('logo-sm')
            ->performOnCollections('logo')
            ->width(128)
            ->height(128)
            ->format('webp')
            ->quality(85)
            ->sharpen(10)
            ->optimize();

        $this
            ->addMediaConversion('logo-md')
            ->performOnCollections('logo')
            ->width(200)
            ->height(200)
            ->format('webp')
            ->quality(85)
            ->sharpen(10)
            ->optimize();

        $this
            ->addMediaConversion('logo-lg')
            ->performOnCollections('logo')
            ->width(400)
            ->height(400)
            ->format('webp')
            ->quality(90)
            ->sharpen(5)
            ->optimize();
    }
}
