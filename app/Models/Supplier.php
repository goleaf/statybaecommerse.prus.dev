<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\SupplierFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

final class Supplier extends Model implements HasMedia
{
    /** @use HasFactory<SupplierFactory> */
    use HasFactory;

    use InteractsWithMedia;
    use SoftDeletes;

    protected $table = 'suppliers';

    protected $fillable = [
        'name',
        'code',
        'contact_email',
        'contact_phone',
        'notes',
        'is_enabled',
    ];

    protected function casts(): array
    {
        return [
            'is_enabled' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        self::saving(static function (Supplier $supplier): void {
            if (trim((string) $supplier->code) === '') {
                $supplier->code = $supplier->generateUniqueCode();
            }
        });

        self::deleting(static function (Supplier $supplier): void {
            if (Schema::hasTable('product_supplier')) {
                $supplier->products()->detach();
            }

            if (Schema::hasTable('variant_inventories') && Schema::hasColumn('variant_inventories', 'supplier_id')) {
                VariantInventory::query()
                    ->where('supplier_id', $supplier->getKey())
                    ->update(['supplier_id' => null]);
            }
        });
    }

    /**
     * @return BelongsToMany<Product, $this>
     */
    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'product_supplier')->withTimestamps();
    }

    /**
     * @return HasMany<VariantInventory, $this>
     */
    public function variantInventories(): HasMany
    {
        return $this->hasMany(VariantInventory::class, 'supplier_id');
    }

    /**
     * @param  Builder<static> $query
     * @return Builder<static>
     */
    public function scopeEnabled(Builder $query): Builder
    {
        return $query->where('is_enabled', true);
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('logo')
            ->singleFile()
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/svg+xml']);
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('logo-sm')
            ->performOnCollections('logo')
            ->width(128)
            ->height(128)
            ->format('webp')
            ->quality(85)
            ->optimize();
    }

    public function generateUniqueCode(): string
    {
        $base = Str::upper(Str::slug((string) ($this->name ?: 'supplier'), '-'));

        if ($base === '') {
            $base = 'SUPPLIER';
        }

        $suffix = 1;

        do {
            $candidate = sprintf('%s-%03d', $base, $suffix);
            $suffix++;
        } while (self::query()
            ->where('code', $candidate)
            ->when(
                $this->exists,
                fn (Builder $query): Builder => $query->whereKeyNot($this->getKey())
            )
            ->exists());

        return $candidate;
    }

    protected static function newFactory(): SupplierFactory
    {
        return SupplierFactory::new();
    }
}
