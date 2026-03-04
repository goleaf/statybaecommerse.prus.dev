<?php

declare(strict_types=1);

namespace App\Filament\Imports;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Services\ProductImages\ProductImageWriteService;
use Filament\Actions\Imports\Exceptions\RowImportFailedException;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Models\Import;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Get;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Number;
use Illuminate\Support\Str;
use Throwable;

class ProductImporter extends BaseImporter
{
    protected static ?string $model = Product::class;

    private ?string $pendingImageUrl = null;

    private mixed $pendingImageState = null;

    /**
     * @var array<string, mixed>
     */
    private array $pendingVariantState = [];

    private const IMPORT_IMAGE_MAX_WIDTH = 1600;

    private const IMPORT_IMAGE_MAX_HEIGHT = 1600;

    private const IMPORT_IMAGE_QUALITY = 85;

    private const IMPORT_IMAGE_DOWNLOAD_TIMEOUT_SECONDS = 300;

    private const IMPORT_IMAGE_CONNECT_TIMEOUT_SECONDS = 30;

    private const VARIANT_ATTRIBUTE_FIELDS = [
        'size_type',
        'color',
        'pack_size',
        'pack_size_type',
        'volume',
        'material',
    ];

    private const SYNC_KEY_FIELDS = [
        'sku'     => 'SKU',
        'barcode' => 'Barcode',
        'slug'    => 'Slug',
        'name'    => 'Name',
    ];

    /**
     * @return array<int, mixed>
     */
    public static function getOptionsFormComponents(): array
    {
        return [
            Placeholder::make('sync_keys_available')
                ->label(__('admin.import_sync_keys_available'))
                ->content(function (Get $get): HtmlString {
                    $columnMap = $get('columnMap');
                    $columnMap = is_array($columnMap) ? $columnMap : [];
                    $lines = [];

                    foreach (self::SYNC_KEY_FIELDS as $field => $label) {
                        $mapped = $columnMap[$field] ?? null;

                        if ($field === 'slug' && blank($mapped) && filled($columnMap['name'] ?? null)) {
                            $mapped = __('admin.import_sync_key_slug_derived', [
                                'column' => $columnMap['name'],
                            ]);
                        }

                        $lines[] = sprintf(
                            '<div class="text-xs text-gray-600 dark:text-gray-300"><strong>%s</strong>: %s</div>',
                            e($label),
                            $mapped !== null && $mapped !== ''
                                ? e((string) $mapped)
                                : e(__('admin.import_sync_key_unmapped'))
                        );
                    }

                    return new HtmlString('<div class="space-y-1">' . implode('', $lines) . '</div>');
                })
                ->visible(fn (Get $get): bool => (bool) $get('should_sync')),

            Repeater::make('sync_keys')
                ->label(__('admin.import_sync_keys'))
                ->helperText(__('admin.import_sync_keys_description'))
                ->schema([
                    Select::make('field')
                        ->label(__('admin.import_sync_key_field'))
                        ->options(self::syncKeyFieldOptions())
                        ->required()
                        ->searchable(),
                ])
                ->default([['field' => 'sku']])
                ->addActionLabel(__('admin.import_sync_key_add'))
                ->reorderable(true)
                ->columns(1)
                ->visible(fn (Get $get): bool => (bool) $get('should_sync')),
        ];
    }

    /**
     * @return array<string, string>
     */
    private static function syncKeyFieldOptions(): array
    {
        return self::SYNC_KEY_FIELDS;
    }

    protected function beforeValidate(): void
    {
        $name = $this->data['name'] ?? null;

        if (($this->data['slug'] ?? null) === null && is_string($name) && $name !== '') {
            $this->data['slug'] = Str::slug($name);
        }

        if ($this->shouldSync() && ($this->record?->exists)) {
            $this->fillMissingSyncValues();
        }

        parent::beforeValidate();
    }

    protected function beforeFill(): void
    {
        $rawImageUrl = $this->data['image_url'] ?? null;
        $this->pendingImageUrl = is_string($rawImageUrl) ? trim($rawImageUrl) : null;

        $this->pendingImageState = $this->data['image'] ?? null;
        $this->pendingVariantState = $this->data;

        if ($this->pendingImageUrl === '') {
            $this->pendingImageUrl = null;
        }

        unset($this->data['image_url'], $this->data['image'], $this->data['volume'], $this->data['material']);

        if ($this->record && ! $this->record->exists) {
            $this->applyVisibilityDefaults();
        }

        $slug = $this->data['slug'] ?? null;
        $name = $this->data['name'] ?? null;

        if (! filled($slug) && is_string($name) && $name !== '') {
            $slug = Str::slug($name);
        }

        if ($this->record && filled($slug)) {
            if (! ($this->record->exists && filled($this->record->slug) && blank($this->data['slug'] ?? null))) {
                $slug = Str::slug((string) $slug);
                $this->record->slug = $this->makeUniqueColumnValue($this->record, 'slug', $slug);
            }
        }
    }

    private function applyVisibilityDefaults(): void
    {
        $defaults = [
            'is_enabled'   => true,
            'status'       => 'published',
            'published_at' => now(),
        ];

        foreach ($defaults as $field => $value) {
            if (
                ! array_key_exists($field, $this->data)
                || $this->data[$field] === null
                || $this->data[$field] === ''
            ) {
                $this->data[$field] = $value;
            }
        }
    }

    private function fillMissingSyncValues(): void
    {
        if (! $this->record) {
            return;
        }

        foreach (['name', 'sku'] as $field) {
            if (
                ! array_key_exists($field, $this->data)
                || $this->data[$field] === null
                || $this->data[$field] === ''
            ) {
                $value = $this->record->getAttribute($field);

                if ($value !== null && $value !== '') {
                    $this->data[$field] = $value;
                }
            }
        }
    }

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('name')
                ->requiredMapping()
                ->rules(['required', 'string']),
            ImportColumn::make('image')
                ->label(__('translations.image'))
                ->examples(['https://example.com/product.jpg'])
                ->ignoreBlankState()
                ->fillRecordUsing(static function (mixed $state): void {}),
            ImportColumn::make('description'),
            ImportColumn::make('short_description'),
            ImportColumn::make('sku')
                ->label(__('admin.labels.sku'))
                ->requiredMapping()
                ->rules(['required']),
            ImportColumn::make('price')
                ->numeric()
                ->rules(['nullable', 'numeric']),
            ImportColumn::make('cost_price')
                ->numeric()
                ->rules(['nullable', 'numeric']),
            ImportColumn::make('manage_stock')
                ->boolean()
                ->ignoreBlankState()
                ->rules(['nullable', 'boolean']),
            ImportColumn::make('stock_quantity')
                ->numeric()
                ->guess(['stock quantity', 'stock_quantity', 'stock', 'sandelyje', 'kiekis'])
                ->ignoreBlankState()
                ->rules(['nullable', 'integer']),
            ImportColumn::make('low_stock_threshold')
                ->numeric()
                ->ignoreBlankState()
                ->rules(['nullable', 'integer']),
            ImportColumn::make('weight')
                ->numeric()
                ->rules(['nullable', 'numeric']),
            ImportColumn::make('length')
                ->numeric()
                ->rules(['nullable', 'numeric']),
            ImportColumn::make('width')
                ->numeric()
                ->rules(['nullable', 'numeric']),
            ImportColumn::make('height')
                ->numeric()
                ->rules(['nullable', 'numeric']),
            ImportColumn::make('size')
                ->label(__('messages.size'))
                ->guess(['size', 'dydis'])
                ->rules(['nullable', 'string', 'max:255']),
            ImportColumn::make('size_type')
                ->label(__('admin.labels.size_type'))
                ->guess(['size type', 'size_type', 'dydzio tipas'])
                ->rules(['nullable', 'string', 'max:255']),
            ImportColumn::make('color')
                ->label(__('translations.color'))
                ->guess(['color', 'colour', 'spalva'])
                ->rules(['nullable', 'string', 'max:255']),
            ImportColumn::make('pack_size')
                ->label(__('attribute.pack_size'))
                ->guess(['pack size', 'pack_size', 'pakuotes dydis', 'volume', 'turis'])
                ->rules(['nullable', 'string', 'max:255']),
            ImportColumn::make('pack_size_type')
                ->label(__('admin.labels.pack_size_type'))
                ->guess(['pack size type', 'pack_size_type', 'pakuotes dydzio tipas'])
                ->rules(['nullable', 'string', 'max:255']),
            ImportColumn::make('volume')
                ->label('Volume')
                ->guess(['volume', 'turis'])
                ->rules(['nullable', 'string', 'max:255']),
            ImportColumn::make('material')
                ->label('Material')
                ->guess(['material', 'medziaga'])
                ->rules(['nullable', 'string', 'max:255']),
            ImportColumn::make('is_enabled')
                ->boolean()
                ->ignoreBlankState()
                ->rules(['nullable', 'boolean']),
            ImportColumn::make('is_featured')
                ->boolean()
                ->ignoreBlankState()
                ->rules(['nullable', 'boolean']),
            ImportColumn::make('published_at')
                ->rules(['nullable', 'date']),
            ImportColumn::make('brand')
                ->relationship(resolveUsing: static function (string $state): ?Brand {
                    return static::resolveBrandFromState($state);
                })
                ->ignoreBlankState(),
            ImportColumn::make('categories')
                ->relationship(resolveUsing: static function (array $state): EloquentCollection {
                    return static::resolveCategoriesFromState($state);
                })
                ->multiple(';')
                ->guess(['category', 'categories', 'kategorija', 'kategorijos'])
                ->ignoreBlankState(),
            ImportColumn::make('is_requestable')
                ->boolean()
                ->ignoreBlankState()
                ->rules(['nullable', 'boolean']),
            ImportColumn::make('minimum_quantity')
                ->numeric()
                ->ignoreBlankState()
                ->rules(['nullable', 'integer']),
            ImportColumn::make('hide_add_to_cart')
                ->boolean()
                ->ignoreBlankState()
                ->rules(['nullable', 'boolean']),
            ImportColumn::make('request_message'),
            ImportColumn::make('barcode'),
            ImportColumn::make('track_inventory')
                ->boolean()
                ->ignoreBlankState()
                ->rules(['nullable', 'boolean']),

            ImportColumn::make('allow_backorder')
                ->boolean()
                ->ignoreBlankState()
                ->rules(['nullable', 'boolean']),
            ImportColumn::make('shipping_class'),
            ImportColumn::make('external_url'),
            ImportColumn::make('available_from')
                ->rules(['nullable', 'date']),
            ImportColumn::make('available_until')
                ->rules(['nullable', 'date']),
            ImportColumn::make('warehouse_quantity')
                ->numeric()
                ->ignoreBlankState()
                ->rules(['nullable', 'integer']),
            ImportColumn::make('status')
                ->ignoreBlankState()
                ->rules(['nullable', 'in:draft,published,archived']),
            ImportColumn::make('image_url')
                ->label(__('admin.labels.image_url'))
                ->ignoreBlankState()
                ->rules(['nullable', 'url:http,https']),
            ImportColumn::make('image')
                ->label(__('admin.labels.image'))
                ->ignoreBlankState()
                ->rules(['nullable', 'url:http,https']),
        ];
    }

    public function resolveRecord(): Product
    {
        if ($this->shouldSync() && ($syncRecord = $this->resolveRecordFromSyncKeys())) {
            return $syncRecord;
        }

        if ($nameRecord = $this->resolveRecordFromName()) {
            return $nameRecord;
        }

        return new Product;
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Your product import has completed and ' . Number::format($import->successful_rows) . ' ' . str('row')->plural($import->successful_rows) . ' imported.';

        if ($failedRowsCount = static::calculateFailedRowsCount($import)) {
            $body .= ' ' . Number::format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to import.';
        }

        return $body;
    }

    public static function getColumnGroups(): array
    {
        return [
            'Basic Information' => [
                'name',
                'image',
                'sku',
                'barcode',
                'status',
            ],
            'Descriptions' => [
                'description',
                'short_description',
                'detailed_description',
            ],
            'Pricing' => [
                'price',
                'cost_price',
                'compare_price',
            ],
            'Inventory' => [
                'manage_stock',
                'stock_quantity',
                'low_stock_threshold',
                'track_inventory',
                'allow_backorder',
                'warehouse_quantity',
                'minimum_quantity',
            ],
            'Dimensions' => [
                'weight',
                'length',
                'width',
                'height',
            ],
            'Attributes' => [
                'size',
                'size_type',
                'color',
                'pack_size',
                'pack_size_type',
                'volume',
                'material',
            ],
            'Relations' => [
                'brand',
                'categories',
                'collection',
            ],
            'Other' => [
                'is_enabled',
                'is_featured',
                'published_at',
                'external_url',
                'image_url',
                'image',
                'available_from',
                'available_until',
            ],
        ];
    }

    protected function afterSave(): void
    {
        if (! $this->record instanceof Product) {
            return;
        }

        $this->upsertVariantForCurrentRow($this->record);

        $imageUrl = $this->pendingImageUrl;

        if (is_string($imageUrl) && trim($imageUrl) !== '') {
            $this->replaceProductImageFromUrl(trim($imageUrl));
        }

        $imageState = $this->pendingImageState;

        if (blank($imageState)) {
            return;
        }

        $paths = $this->normalizeImagePaths($imageState);

        if ($paths === []) {
            return;
        }

        $this->attachImages($this->record, $paths);
    }

    private function resolveRecordFromName(): ?Product
    {
        $name = $this->toNullableString($this->data['name'] ?? null);

        if ($name === null) {
            return null;
        }

        $normalizedName = Str::lower(Str::squish($name));
        $slug = Str::slug($name);

        return Product::query()
            ->withoutGlobalScopes()
            ->where(function ($query) use ($normalizedName, $slug): void {
                $query->whereRaw('LOWER(TRIM(name)) = ?', [$normalizedName]);

                if ($slug !== '') {
                    $query->orWhere('slug', $slug);
                }
            })
            ->orderBy('id')
            ->first();
    }

    /**
     * @return array<int, string>
     */
    private function normalizeImagePaths(mixed $state): array
    {
        $items = is_array($state) ? $state : [$state];
        $paths = [];

        foreach ($items as $item) {
            if (is_array($item)) {
                foreach ($item as $nested) {
                    if (is_scalar($nested)) {
                        $paths[] = (string) $nested;
                    }
                }

                continue;
            }

            if (! is_scalar($item)) {
                continue;
            }

            $value = trim((string) $item);

            if ($value === '') {
                continue;
            }

            if (str_starts_with($value, 'data:')) {
                $paths[] = $value;

                continue;
            }

            if (str_contains($value, ';') || str_contains($value, '|')) {
                $parts = preg_split('/[;|]/', $value) ?: [];
                foreach ($parts as $part) {
                    $part = trim($part);
                    if ($part !== '') {
                        $paths[] = $part;
                    }
                }

                continue;
            }

            $paths[] = $value;
        }

        $paths = array_filter(array_map('trim', $paths), static fn (string $path): bool => $path !== '');

        return array_values(array_unique($paths));
    }

    /**
     * @param array<int, string> $paths
     */
    private function attachImages(Product $product, array $paths): void
    {
        $this->imageWriteService()->appendPaths($product, $paths, $this->resolveImageAltText($product));
    }

    private function resolveImageAltText(Product $product): ?string
    {
        $name = $this->data['name'] ?? $product->name;

        if (! is_string($name)) {
            return null;
        }

        $name = trim($name);

        return $name !== '' ? $name : null;
    }

    private function shouldSync(): bool
    {
        return (bool) ($this->options['should_sync'] ?? false);
    }

    private function upsertVariantForCurrentRow(Product $product): void
    {
        if (! $this->shouldCreateVariant()) {
            return;
        }

        $sku = $this->toNullableString($this->variantStateValue('sku'));
        $barcode = $this->toNullableString($this->variantStateValue('barcode'));
        $size = $this->toNullableString($this->variantStateValue('size'));
        $price = $this->toNullableFloat($this->variantStateValue('price'))
            ?? $this->toNullableFloat($product->price)
            ?? 0.0;
        $costPrice = $this->toNullableFloat($this->variantStateValue('cost_price'));
        $weight = $this->toNullableFloat($this->variantStateValue('weight'));
        $stockQuantity = $this->toNullableInteger($this->variantStateValue('stock_quantity'))
            ?? $this->toNullableInteger($this->variantStateValue('stock'))
            ?? 0;
        $isEnabled = $this->toNullableBoolean($this->variantStateValue('is_enabled'))
            ?? $this->toNullableBoolean($this->variantStateValue('status')) // defensive fallback for malformed CSVs
            ?? true;
        $trackInventory = $this->toNullableBoolean($this->variantStateValue('track_inventory'))
            ?? $this->toNullableBoolean($this->variantStateValue('manage_stock'))
            ?? true;
        $allowBackorder = $this->toNullableBoolean($this->variantStateValue('allow_backorder'))
            ?? false;
        $lowStockThreshold = $this->toNullableInteger($this->variantStateValue('low_stock_threshold')) ?? 0;

        $attributes = $this->extractVariantAttributes();
        $variantName = $this->buildVariantName($product, $attributes, $size, $sku);

        $variantQuery = ProductVariant::query()
            ->withoutGlobalScopes()
            ->where('product_id', $product->getKey());

        if ($sku !== null) {
            $variantQuery->where('sku', $sku);
        } elseif ($barcode !== null) {
            $variantQuery->where('barcode', $barcode);
        } else {
            $variantQuery->where('name', $variantName);

            if ($size !== null) {
                $variantQuery->where('size', $size);
            } else {
                $variantQuery->whereNull('size');
            }
        }

        $variant = $variantQuery->first();
        $isNewVariant = ! $variant instanceof ProductVariant;

        if (! $variant instanceof ProductVariant) {
            $variant = new ProductVariant;
            $variant->product_id = $product->getKey();

            $hasExistingVariants = ProductVariant::query()
                ->withoutGlobalScopes()
                ->where('product_id', $product->getKey())
                ->exists();

            $variant->is_default = ! $hasExistingVariants;
            $variant->is_default_variant = ! $hasExistingVariants;
        }

        $variant->fill([
            'product_id'          => $product->getKey(),
            'name'                => $variantName,
            'sku'                 => $sku,
            'barcode'             => $barcode,
            'price'               => $price,
            'cost_price'          => $costPrice,
            'stock_quantity'      => $stockQuantity,
            'weight'              => $weight,
            'track_inventory'     => $trackInventory,
            'allow_backorder'     => $allowBackorder,
            'low_stock_threshold' => $lowStockThreshold,
            'size'                => $size,
            'is_enabled'          => $isEnabled,
            'attributes'          => $attributes !== [] ? $attributes : null,
        ]);

        $variant->save();
        $this->attachVariantToProduct($product, $variant);

        $this->syncParentProductVariantSnapshot($product, $variant, $isNewVariant);
    }

    private function shouldCreateVariant(): bool
    {
        $sku = $this->toNullableString($this->variantStateValue('sku'));
        $size = $this->toNullableString($this->variantStateValue('size'));
        $color = $this->toNullableString($this->variantStateValue('color'));
        $packSize = $this->toNullableString($this->variantStateValue('pack_size'));
        $volume = $this->toNullableString($this->variantStateValue('volume'));
        $price = $this->toNullableFloat($this->variantStateValue('price'));
        $stockQuantity = $this->toNullableInteger($this->variantStateValue('stock_quantity'))
            ?? $this->toNullableInteger($this->variantStateValue('stock'));

        return $sku !== null
            || $size !== null
            || $color !== null
            || $packSize !== null
            || $volume !== null
            || $price !== null
            || $stockQuantity !== null;
    }

    /**
     * @return array<string, string>
     */
    private function extractVariantAttributes(): array
    {
        $attributes = [];

        foreach (self::VARIANT_ATTRIBUTE_FIELDS as $field) {
            $value = $this->toNullableString($this->variantStateValue($field));

            if ($value === null) {
                continue;
            }

            $attributes[$field] = $value;
        }

        return $attributes;
    }

    /**
     * @param array<string, string> $attributes
     */
    private function buildVariantName(Product $product, array $attributes, ?string $size, ?string $sku): string
    {
        $parts = [];

        foreach (['color', 'size', 'pack_size', 'volume', 'material'] as $field) {
            if ($field === 'size' && $size !== null) {
                $parts[] = $size;

                continue;
            }

            if (! array_key_exists($field, $attributes)) {
                continue;
            }

            $parts[] = $attributes[$field];
        }

        if ($parts !== []) {
            return implode(' / ', array_values(array_unique($parts)));
        }

        if ($sku !== null) {
            return $sku;
        }

        $name = $this->toNullableString($this->variantStateValue('name')) ?? $this->toNullableString($product->name);

        return $name ?? 'Variant';
    }

    private function syncParentProductVariantSnapshot(Product $product, ProductVariant $variant, bool $isNewVariant): void
    {
        $baseVariantQuery = ProductVariant::query()
            ->withoutGlobalScopes()
            ->where('product_id', $product->getKey());

        $minPrice = (clone $baseVariantQuery)
            ->whereNotNull('price')
            ->min('price');

        $totalStock = (int) ((clone $baseVariantQuery)->sum('stock_quantity'));
        $defaultSku = (clone $baseVariantQuery)
            ->whereNotNull('sku')
            ->where('sku', '!=', '')
            ->orderByDesc('is_default_variant')
            ->orderBy('id')
            ->value('sku');

        $productData = ['stock_quantity' => $totalStock];

        if ($minPrice !== null) {
            $productData['price'] = (float) $minPrice;
        }

        if (is_string($defaultSku) && trim($defaultSku) !== '') {
            $productData['sku'] = trim($defaultSku);
        }

        if ($isNewVariant) {
            $variantCount = (clone $baseVariantQuery)->count();
            $productData['manage_stock'] = true;

            if ($variantCount === 1) {
                $productData['slug'] = filled($product->slug)
                    ? $product->slug
                    : Str::slug((string) $product->name);
            }
        }

        $product->forceFill($productData)->saveQuietly();
    }

    private function attachVariantToProduct(Product $product, ProductVariant $variant): void
    {
        if (! Schema::hasTable('product_variant_product')) {
            return;
        }

        $product->variants()->syncWithoutDetaching([$variant->getKey()]);
    }

    private function variantStateValue(string $key): mixed
    {
        return $this->pendingVariantState[$key] ?? null;
    }

    private function toNullableString(mixed $value): ?string
    {
        if (is_string($value)) {
            $value = trim($value);

            return $value !== '' ? $value : null;
        }

        if (is_numeric($value)) {
            return (string) $value;
        }

        return null;
    }

    private function toNullableFloat(mixed $value): ?float
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (! is_numeric($value)) {
            return null;
        }

        return (float) $value;
    }

    private function toNullableInteger(mixed $value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (! is_numeric($value)) {
            return null;
        }

        return (int) $value;
    }

    private function toNullableBoolean(mixed $value): ?bool
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_bool($value)) {
            return $value;
        }

        if (is_numeric($value)) {
            return (int) $value === 1;
        }

        if (! is_string($value)) {
            return null;
        }

        $normalized = Str::lower(trim($value));

        return match ($normalized) {
            '1', 'true', 'yes', 'y' => true,
            '0', 'false', 'no', 'n' => false,
            default => null,
        };
    }

    private function resolveRecordFromSyncKeys(): ?Product
    {
        $keys = $this->getSyncKeys();

        if ($keys === []) {
            return null;
        }

        foreach ($keys as $key) {
            $value = $this->getSyncKeyValue($key);

            if ($value === null || $value === '') {
                continue;
            }

            $matches = Product::query()
                ->withoutGlobalScopes()
                ->where($key, $value)
                ->limit(2)
                ->get();

            if ($matches->count() > 1) {
                throw new RowImportFailedException(__('admin.import_sync_key_ambiguous', [
                    'field' => $this->getSyncKeyLabel($key),
                    'value' => is_scalar($value) ? (string) $value : '',
                ]));
            }

            if ($matches->count() === 1) {
                return $matches->first();
            }
        }

        return null;
    }

    /**
     * @return array<int, string>
     */
    private function getSyncKeys(): array
    {
        $raw = $this->options['sync_keys'] ?? [];

        if (! is_array($raw)) {
            return [];
        }

        $keys = [];

        foreach ($raw as $item) {
            if (is_string($item)) {
                $field = $item;
            } elseif (is_array($item)) {
                $field = $item['field'] ?? null;
            } else {
                $field = null;
            }

            if (! is_string($field)) {
                continue;
            }

            $field = trim($field);

            if ($field === '') {
                continue;
            }

            if (! array_key_exists($field, self::SYNC_KEY_FIELDS)) {
                continue;
            }

            $keys[] = $field;
        }

        return array_values(array_unique($keys));
    }

    private function getSyncKeyValue(string $key): mixed
    {
        $value = $this->data[$key] ?? null;

        if (($value === null || $value === '') && $key === 'slug') {
            $name = $this->data['name'] ?? null;
            if (is_string($name) && $name !== '') {
                $value = Str::slug($name);
            }
        }

        if (is_string($value)) {
            $value = trim($value);
        }

        if ($value === '' || $value === null) {
            return null;
        }

        if ($key === 'slug' && is_string($value)) {
            return Str::slug($value);
        }

        return $value;
    }

    private function getSyncKeyLabel(string $key): string
    {
        return self::SYNC_KEY_FIELDS[$key] ?? Str::headline($key);
    }

    private static function resolveBrandFromState(mixed $state): ?Brand
    {
        if ($state === null) {
            return null;
        }

        $raw = is_string($state) ? trim($state) : $state;

        if ($raw === '' || $raw === null) {
            return null;
        }

        $query = Brand::query()->withoutGlobalScopes();

        if (is_numeric($raw)) {
            $brand = $query->find((int) $raw);

            if ($brand) {
                return $brand;
            }
        }

        $name = is_string($raw) ? $raw : (string) $raw;
        $slug = Str::slug($name);

        $brand = $query->where('slug', $slug)->first()
            ?? $query->where('name', $name)->first();

        if ($brand) {
            return $brand;
        }

        return Brand::query()->create([
            'name'        => $name,
            'slug'        => $slug,
            'is_enabled'  => true,
            'is_active'   => true,
            'is_visible'  => true,
            'is_featured' => false,
            'is_premium'  => false,
            'sort_order'  => 0,
        ]);
    }

    /**
     * @param array<int, mixed> $state
     */
    private static function resolveCategoriesFromState(array $state): EloquentCollection
    {
        $names = collect($state)
            ->flatMap(function ($value): array {
                if (! is_string($value)) {
                    return [$value];
                }

                $value = trim($value);

                if ($value === '') {
                    return [];
                }

                if (str_contains($value, ';') || str_contains($value, '|')) {
                    return preg_split('/[;|]/', $value) ?: [$value];
                }

                return [$value];
            })
            ->map(fn ($value) => is_string($value) ? trim($value) : $value)
            ->filter(fn ($value) => filled($value))
            ->map(fn ($value) => (string) $value)
            ->values();

        if ($names->isEmpty()) {
            return new EloquentCollection;
        }

        $categories = new EloquentCollection;

        foreach ($names as $name) {
            $category = static::resolveCategoryPath($name);

            if ($category instanceof Category) {
                $categories->push($category);
            }
        }

        return $categories;
    }

    private static function resolveCategoryPath(string $path): ?Category
    {
        $segments = collect(preg_split('/\s*\/\s*/', $path) ?: [])
            ->map(fn (string $segment): string => trim($segment))
            ->filter(fn (string $segment): bool => $segment !== '')
            ->values();

        if ($segments->isEmpty()) {
            return null;
        }

        $parent = null;

        foreach ($segments as $segment) {
            $category = static::resolveCategorySegment($segment, $parent);

            if (! $category) {
                $category = Category::query()->create([
                    'name'         => $segment,
                    'slug'         => Category::generateUniqueSlug($segment),
                    'parent_id'    => $parent?->getKey(),
                    'sort_order'   => 0,
                    'is_enabled'   => true,
                    'is_active'    => true,
                    'is_visible'   => true,
                    'is_featured'  => false,
                    'show_in_menu' => true,
                ]);
            }

            $parent = $category;
        }

        return $parent;
    }

    private static function resolveCategorySegment(string $segment, ?Category $parent): ?Category
    {
        $segment = trim($segment);

        if ($segment === '') {
            return null;
        }

        $query = Category::query()->withoutGlobalScopes();

        if ($parent instanceof Category) {
            $query->where('parent_id', $parent->getKey());
        } else {
            $query->whereNull('parent_id');
        }

        $category = (clone $query)->where('name', $segment)->first();

        if (! $category) {
            $slug = Str::slug($segment);

            if ($slug !== '') {
                $category = (clone $query)->where('slug', $slug)->first();
            }
        }

        return $category;
    }

    private function replaceProductImageFromUrl(string $imageUrl): void
    {
        if (! $this->record instanceof Product || ! $this->record->exists) {
            return;
        }

        try {
            $contents = $this->downloadImageContents($imageUrl);

            // Don't fail the row if the image can't be downloaded: import the product without an image.
            if ($contents === null) {
                return;
            }

            $extension = $this->resolveImageExtension($imageUrl, $contents['content_type'] ?? null);
            $path = 'product-images/' . $this->record->getKey() . '/import-' . Str::uuid() . '.' . $extension;

            $resizedImageContents = $this->resizeImageContents($contents['body'], $extension);

            Storage::disk('public')->put($path, $resizedImageContents);

            $this->imageWriteService()->replaceWithPath($this->record, $path, $this->record->name);
        } catch (Throwable) {
            // Best-effort image import. Ignore errors so the product row still imports successfully.
            return;
        }
    }

    /**
     * @return array{body:string, content_type:string|null}|null
     */
    private function downloadImageContents(string $imageUrl): ?array
    {
        try {
            $response = Http::connectTimeout(self::IMPORT_IMAGE_CONNECT_TIMEOUT_SECONDS)
                ->timeout(self::IMPORT_IMAGE_DOWNLOAD_TIMEOUT_SECONDS)
                ->retry(1, 200)
                ->get($imageUrl);
        } catch (ConnectionException) {
            return null;
        }

        if (! $response->successful()) {
            return null;
        }

        $body = $response->body();

        if ($body === '') {
            return null;
        }

        return [
            'body'         => $body,
            'content_type' => $response->header('content-type'),
        ];
    }

    private function resolveImageExtension(string $imageUrl, ?string $contentType): string
    {
        if (is_string($contentType) && $contentType !== '') {
            $normalized = Str::lower($contentType);

            return match (true) {
                str_contains($normalized, 'png')  => 'png',
                str_contains($normalized, 'webp') => 'webp',
                str_contains($normalized, 'gif')  => 'gif',
                str_contains($normalized, 'jpeg'), str_contains($normalized, 'jpg') => 'jpg',
                default => 'jpg',
            };
        }

        try {
            $path = parse_url($imageUrl, PHP_URL_PATH);
        } catch (Throwable) {
            $path = null;
        }

        if (! is_string($path) || $path === '') {
            return 'jpg';
        }

        $extension = Str::lower(pathinfo($path, PATHINFO_EXTENSION));

        if (! in_array($extension, ['jpg', 'jpeg', 'png', 'webp', 'gif'], true)) {
            return 'jpg';
        }

        return $extension === 'jpeg' ? 'jpg' : $extension;
    }

    private function resizeImageContents(string $imageContents, string $extension): string
    {
        if (! function_exists('imagecreatefromstring')) {
            return $imageContents;
        }

        $sourceImage = @imagecreatefromstring($imageContents);

        if ($sourceImage === false) {
            return $imageContents;
        }

        $sourceWidth = imagesx($sourceImage);
        $sourceHeight = imagesy($sourceImage);

        if ($sourceWidth <= self::IMPORT_IMAGE_MAX_WIDTH && $sourceHeight <= self::IMPORT_IMAGE_MAX_HEIGHT) {
            imagedestroy($sourceImage);

            return $imageContents;
        }

        $scaleRatio = min(
            self::IMPORT_IMAGE_MAX_WIDTH / max($sourceWidth, 1),
            self::IMPORT_IMAGE_MAX_HEIGHT / max($sourceHeight, 1),
            1
        );

        $targetWidth = max((int) floor($sourceWidth * $scaleRatio), 1);
        $targetHeight = max((int) floor($sourceHeight * $scaleRatio), 1);
        $targetImage = imagecreatetruecolor($targetWidth, $targetHeight);

        if ($targetImage === false) {
            imagedestroy($sourceImage);

            return $imageContents;
        }

        if (in_array($extension, ['png', 'gif', 'webp'], true)) {
            imagealphablending($targetImage, false);
            imagesavealpha($targetImage, true);
            $transparentColor = imagecolorallocatealpha($targetImage, 0, 0, 0, 127);
            imagefilledrectangle($targetImage, 0, 0, $targetWidth, $targetHeight, $transparentColor);
        }

        imagecopyresampled(
            $targetImage,
            $sourceImage,
            0,
            0,
            0,
            0,
            $targetWidth,
            $targetHeight,
            $sourceWidth,
            $sourceHeight
        );

        ob_start();

        $wasEncoded = match ($extension) {
            'png'   => imagepng($targetImage, null, 6),
            'gif'   => imagegif($targetImage),
            'webp'  => function_exists('imagewebp') ? imagewebp($targetImage, null, self::IMPORT_IMAGE_QUALITY) : imagejpeg($targetImage, null, self::IMPORT_IMAGE_QUALITY),
            default => imagejpeg($targetImage, null, self::IMPORT_IMAGE_QUALITY),
        };

        $encodedContents = (string) ob_get_clean();

        imagedestroy($sourceImage);
        imagedestroy($targetImage);

        if (! $wasEncoded || $encodedContents === '') {
            return $imageContents;
        }

        return $encodedContents;
    }

    private function imageWriteService(): ProductImageWriteService
    {
        return app(ProductImageWriteService::class);
    }
}
