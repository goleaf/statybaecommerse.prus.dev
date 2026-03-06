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
        'company_code',
        'code',
        'vat_code',
        'contact_person',
        'contact_email',
        'contact_phone',
        'website',
        'address',
        'city',
        'postal_code',
        'country',
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
            $supplier->company_code = $supplier->resolveCompanyCode();

            $normalizedCode = $supplier->normalizeSystemCode($supplier->code);

            if ($normalizedCode === null) {
                $supplier->code = $supplier->generateUniqueCode();

                return;
            }

            if ($supplier->isCodeTaken($normalizedCode)) {
                $supplier->code = $supplier->generateUniqueCode($normalizedCode);

                return;
            }

            $supplier->code = $normalizedCode;
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
            ->useDisk('public')
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

    public function generateUniqueCode(?string $source = null): string
    {
        $base = Str::slug((string) ($source ?: $this->name ?: 'supplier'));

        if ($base === '') {
            $base = 'supplier';
        }

        $candidate = $base;
        $suffix = 2;

        while ($this->isCodeTaken($candidate)) {
            $candidate = sprintf('%s-%d', $base, $suffix);
            $suffix++;
        }

        return $candidate;
    }

    private function isCodeTaken(string $code): bool
    {
        return self::query()
            ->where('code', $code)
            ->when(
                $this->exists,
                fn (Builder $query): Builder => $query->whereKeyNot($this->getKey())
            )
            ->exists();
    }

    private function resolveCompanyCode(): string
    {
        $normalized = $this->normalizeCompanyCode($this->company_code)
            ?? $this->normalizeCompanyCode($this->code);

        if ($normalized !== null) {
            return $normalized;
        }

        $fallback = Str::upper(Str::slug((string) ($this->name ?: 'supplier'), ''));

        return $fallback !== '' ? $fallback : 'SUPPLIER';
    }

    private function normalizeCompanyCode(mixed $value): ?string
    {
        if (! is_string($value)) {
            return null;
        }

        $trimmed = trim($value);

        if ($trimmed === '') {
            return null;
        }

        $compact = preg_replace('/\s+/', '', $trimmed);

        if (! is_string($compact) || $compact === '') {
            return null;
        }

        return Str::upper($compact);
    }

    private function normalizeSystemCode(mixed $value): ?string
    {
        if (! is_string($value)) {
            return null;
        }

        $slug = Str::slug($value);

        return $slug !== '' ? $slug : null;
    }

    protected static function newFactory(): SupplierFactory
    {
        return SupplierFactory::new();
    }
}
