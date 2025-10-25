<?php declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\OrdersByName;
use App\Models\Scopes\ActiveScope;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

/**
 * ProductImage model exposing streamlined attributes for storefront galleries
 * while maintaining compatibility with legacy consumers.
 */
#[ScopedBy([ActiveScope::class])]
final class ProductImage extends Model
{
    /** @use HasFactory<\Database\Factories\ProductImageFactory> */
    use HasFactory;

    use OrdersByName {
        scopeOrderedByName as scopeOrderedByNameFromTrait;
    }

    /**
     * Column leveraged by the OrdersByName trait when alphabetical ordering is requested.
     */
    protected string $nameColumn = 'title';

    /**
     * Explicit table declaration keeps joins predictable during reporting.
     */
    protected $table = 'product_images';

    /**
     * Mass assignable attributes supported by the refreshed schema.
     * The legacy columns are handled through accessors/mutators so existing
     * factories and seeds remain valid.
     */
    protected $fillable = [
        'product_id',
        'product_variant_id',
        'title',
        'alt',
        'path',
        'position',
        'meta',
    ];

    /**
     * Provide sensible defaults for optional JSON metadata to prevent null handling downstream.
     */
    protected $attributes = [
        'meta' => '[]',
    ];

    /**
     * Cast integer identifiers and metadata payloads to native PHP types.
     */
    protected function casts(): array
    {
        return [
            'product_id' => 'int',
            'product_variant_id' => 'int',
            'position' => 'int',
            'meta' => 'array',
        ];
    }

    /**
     * Primary product association used for default galleries.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Optional variant relationship allowing per-variant imagery.
     */
    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }

    /**
     * Scope for filtering images by their owning product identifier.
     */
    public function scopeForProduct(Builder $query, int $productId): Builder
    {
        return $query->where('product_id', $productId);
    }

    /**
     * Scope to restrict the query to records flagged as active through the legacy column.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to order records using the new position attribute while falling back to the identifier for determinism.
     */
    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('position')->orderBy('id');
    }

    /**
     * Scope to fetch the primary (first) image for a product.
     */
    public function scopePrimary(Builder $query): Builder
    {
        return $query->ordered()->limit(1);
    }

    /**
     * Override the shared orderedByName scope so it gracefully falls back through multiple columns.
     */
    public function scopeOrderedByName(Builder $query, string $direction = 'asc'): Builder
    {
        $direction = strtolower($direction) === 'desc' ? 'desc' : 'asc';

        $columns = array_filter(
            ['title', 'alt', 'name'],
            fn (string $column): bool => Schema::hasColumn($this->getTable(), $column)
        );

        if ($columns === []) {
            return $this->scopeOrderedByNameFromTrait($query, $direction);
        }

        $grammar = $query->getQuery()->getGrammar();
        $wrapped = array_map(
            static fn (string $column): string => sprintf("NULLIF(%s, '')", $grammar->wrap($column)),
            $columns,
        );

        $query->orderByRaw(sprintf('COALESCE(%s) %s', implode(', ', $wrapped), $direction));

        return $query->orderBy('position')->orderBy('id');
    }

    /**
     * Determine if the current instance is the leading image for its product.
     */
    public function isPrimary(): bool
    {
        if ((int) $this->position === 0) {
            return true;
        }

        if (! $this->product_id) {
            return false;
        }

        $firstId = self::query()
            ->forProduct((int) $this->product_id)
            ->ordered()
            ->value('id');

        return $firstId !== null && $firstId === $this->getKey();
    }

    /**
     * Resolve the preferred alt text, falling back to the owning product name when possible.
     */
    public function getAltTextOrDefault(): string
    {
        $alt = (string) ($this->alt ?? '');

        if ($alt !== '') {
            return $alt;
        }

        $product = $this->relationLoaded('product') ? $this->getRelation('product') : $this->product;

        return $product instanceof Product
            ? sprintf('%s image', $product->name)
            : 'Product image';
    }

    /**
     * Accessor exposing a consistently formatted public URL for the image.
     */
    public function getUrlAttribute(): string
    {
        return $this->resolvePublicUrl((string) ($this->path ?? ''));
    }

    /**
     * Accessor returning the absolute storage path for filesystem checks.
     */
    public function getFullPathAttribute(): string
    {
        return storage_path('app/public/' . ltrim((string) ($this->path ?? ''), '/'));
    }

    /**
     * Accessor indicating whether the file exists on the configured storage disk.
     */
    public function getExistsOnDiskAttribute(): bool
    {
        $disk = (string) config('filesystems.default', 'public');

        return $this->path !== null
            && Storage::disk($disk)->exists((string) $this->path);
    }

    /**
     * Mutator syncing the new alt column with the legacy alt_text attribute.
     */
    protected function alt(): Attribute
    {
        return Attribute::make(
            get: fn ($value, array $attributes): ?string => $value ?? ($attributes['alt_text'] ?? null),
            set: static function ($value): array {
                $stringValue = $value === null ? null : (string) $value;

                return [
                    'alt' => $stringValue,
                    'alt_text' => $stringValue,
                ];
            },
        );
    }

    /**
     * Mutator keeping the legacy sort_order column aligned with the new position attribute.
     */
    protected function position(): Attribute
    {
        return Attribute::make(
            get: fn ($value, array $attributes): int => (int) ($value ?? ($attributes['sort_order'] ?? 0)),
            set: static function ($value): array {
                $intValue = (int) $value;

                return [
                    'position' => $intValue,
                    'sort_order' => $intValue,
                ];
            },
        );
    }

    /**
     * Maintain backwards compatibility for code paths still referencing alt_text directly.
     */
    protected function altText(): Attribute
    {
        return Attribute::make(
            get: fn ($value, array $attributes): ?string => $attributes['alt'] ?? $value,
            set: static function ($value): array {
                $stringValue = $value === null ? null : (string) $value;

                return [
                    'alt' => $stringValue,
                    'alt_text' => $stringValue,
                ];
            },
        );
    }

    /**
     * Maintain backwards compatibility for code paths still using sort_order.
     */
    protected function sortOrder(): Attribute
    {
        return Attribute::make(
            get: fn ($value, array $attributes): int => (int) ($attributes['position'] ?? $value ?? 0),
            set: static function ($value): array {
                $intValue = (int) $value;

                return [
                    'position' => $intValue,
                    'sort_order' => $intValue,
                ];
            },
        );
    }

    /**
     * Normalise the meta payload so callers always receive an array.
     */
    protected function meta(): Attribute
    {
        return Attribute::make(
            get: static fn ($value): array => is_array($value) ? $value : (json_decode((string) $value, true) ?: []),
            set: static fn ($value): array => ['meta' => $value ?? []],
        );
    }

    /**
     * Internal helper turning stored paths into publicly accessible URLs.
     */
    private function resolvePublicUrl(string $path): string
    {
        if ($path === '') {
            return $path;
        }

        if (str_contains($path, '://')) {
            return $path;
        }

        if (str_starts_with($path, '/')) {
            return asset(ltrim($path, '/'));
        }

        $defaultDisk = (string) config('filesystems.default', 'public');
        $disks = array_unique([$defaultDisk, 'public']);

        foreach ($disks as $disk) {
            if (Storage::disk($disk)->exists($path)) {
                return Storage::disk($disk)->url($path);
            }
        }

        return Storage::disk('public')->url($path);
    }
}
