<?php

declare(strict_types=1);

namespace App\Filament\Imports;

use App\Models\Attribute;
use App\Models\AttributeValue;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Supplier;
use App\Models\VariantAttributeValue;
use App\Services\ProductImages\ProductImageWriteService;
use Carbon\CarbonInterface;
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

    private const FRONTEND_OPTION_FIELDS = [
        'color',
        'size',
        'size_type',
        'pack_size',
        'pack_size_type',
        'weight',
        'length',
        'width',
        'height',
    ];

    private ?string $pendingImageUrl = null;

    private mixed $pendingImageState = null;

    private mixed $pendingCategoriesState = null;

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
        'weight',
        'length',
        'width',
        'height',
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
        $this->pendingCategoriesState = $this->data['categories'] ?? null;
        $this->pendingVariantState = $this->data;

        if ($this->pendingImageUrl === '') {
            $this->pendingImageUrl = null;
        }

        unset($this->data['image_url'], $this->data['image'], $this->data['volume'], $this->data['material']);
        unset($this->data['supplier'], $this->data['categories']);

        if ($this->record && ! $this->record->exists) {
            $this->applyVisibilityDefaults();
        }

        $this->applyPublishedAtFallbackForVisibleStatuses();

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

    private function applyPublishedAtFallbackForVisibleStatuses(): void
    {
        $status = $this->toNullableString($this->data['status'] ?? null)
            ?? $this->toNullableString($this->record?->status);

        if ($status === null || ! in_array(Str::lower($status), ['published', 'active'], true)) {
            return;
        }

        $incomingPublishedAt = $this->data['published_at'] ?? null;

        if ($incomingPublishedAt instanceof CarbonInterface) {
            return;
        }

        if (is_string($incomingPublishedAt) && trim($incomingPublishedAt) !== '') {
            return;
        }

        if ($this->record?->published_at instanceof CarbonInterface) {
            return;
        }

        $this->data['published_at'] = now();
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
                ->castStateUsing(static fn (mixed $originalState, mixed $state): ?float => self::normalizeImportedNumericState($originalState, $state))
                ->rules(['nullable', 'numeric']),
            ImportColumn::make('length')
                ->numeric()
                ->castStateUsing(static fn (mixed $originalState, mixed $state): ?float => self::normalizeImportedNumericState($originalState, $state))
                ->rules(['nullable', 'numeric']),
            ImportColumn::make('width')
                ->numeric()
                ->castStateUsing(static fn (mixed $originalState, mixed $state): ?float => self::normalizeImportedNumericState($originalState, $state))
                ->rules(['nullable', 'numeric']),
            ImportColumn::make('height')
                ->numeric()
                ->castStateUsing(static fn (mixed $originalState, mixed $state): ?float => self::normalizeImportedNumericState($originalState, $state))
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
                ->ignoreBlankState()
                ->rules(['nullable', 'date']),
            ImportColumn::make('seo_title'),
            ImportColumn::make('seo_description'),
            ImportColumn::make('brand')
                ->relationship(resolveUsing: static function (string $state): ?Brand {
                    return static::resolveBrandFromState($state);
                })
                ->ignoreBlankState(),
            ImportColumn::make('categories')
                ->guess(['category', 'categories', 'kategorija', 'kategorijos'])
                ->ignoreBlankState()
                ->fillRecordUsing(static function (mixed $state): void {}),
            ImportColumn::make('supplier')
                ->label(__('admin.suppliers.model_label'))
                ->guess(['supplier', 'supplier_name', 'suppliers', 'tiekejas', 'tiekejai'])
                ->ignoreBlankState()
                ->rules(['nullable', 'string', 'max:255']),
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
            ImportColumn::make('meta_title'),
            ImportColumn::make('meta_description'),
            ImportColumn::make('meta_keywords'),
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
        ];
    }

    public function resolveRecord(): Product
    {
        if ($this->shouldSync()) {
            $syncRecord = $this->resolveRecordFromSyncKeys();

            if ($syncRecord instanceof Product) {
                return $syncRecord;
            }

            if ($this->requiresExistingSyncMatch()) {
                throw new RowImportFailedException('No existing product matched configured sync keys.');
            }
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
                'supplier',
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

        $this->syncCategoriesForCurrentRow($this->record);
        $this->upsertVariantForCurrentRow($this->record);
        $this->syncSupplierForCurrentRow($this->record);

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

        $resolvedPaths = $this->resolveAttachableImagePaths($this->record, $paths);

        if ($resolvedPaths === []) {
            return;
        }

        $this->attachImages($this->record, $resolvedPaths);
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

    /**
     * @param  array<int, string> $paths
     * @return array<int, string>
     */
    private function resolveAttachableImagePaths(Product $product, array $paths): array
    {
        $resolved = [];

        foreach ($paths as $path) {
            $normalizedPath = trim($path);

            if ($normalizedPath === '') {
                continue;
            }

            if ($this->isRemoteImageUrl($normalizedPath)) {
                $storedPath = $this->downloadAndStoreProductImageFromUrl($product, $normalizedPath);

                $resolved[] = $storedPath ?? $normalizedPath;

                continue;
            }

            $resolved[] = $normalizedPath;
        }

        return array_values(array_unique($resolved));
    }

    private function isRemoteImageUrl(string $path): bool
    {
        return str_starts_with($path, 'http://') || str_starts_with($path, 'https://');
    }

    private function shouldSync(): bool
    {
        return (bool) ($this->options['should_sync'] ?? false);
    }

    private function requiresExistingSyncMatch(): bool
    {
        return (bool) ($this->options['require_existing_sync_match'] ?? false);
    }

    private function upsertVariantForCurrentRow(Product $product): void
    {
        if (! $this->shouldCreateVariant()) {
            return;
        }

        $sku = $this->toNullableString($this->variantStateValue('sku'));
        $barcode = $this->toNullableString($this->variantStateValue('barcode'));
        $size = $this->toNullableString($this->variantStateValue('size'));
        $sizeType = $this->toNullableString($this->variantStateValue('size_type'));
        $packSize = $this->toNullableString($this->variantStateValue('pack_size'));
        $packSizeType = $this->toNullableString($this->variantStateValue('pack_size_type'));
        $color = $this->toNullableString($this->variantStateValue('color'));
        $price = $this->toNullableFloat($this->variantStateValue('price'))
            ?? $this->toNullableFloat($product->price)
            ?? 0.0;
        $costPrice = $this->toNullableFloat($this->variantStateValue('cost_price'));
        $weightState = $this->variantStateValue('weight');
        $lengthState = $this->variantStateValue('length');
        $widthState = $this->variantStateValue('width');
        $heightState = $this->variantStateValue('height');
        $weight = $this->toNullableFloat($weightState);
        $length = $this->toNullableFloat($lengthState);
        $width = $this->toNullableFloat($widthState);
        $height = $this->toNullableFloat($heightState);
        $weightIdentity = $this->normalizeVariantNumericIdentityValue($weightState, $weight);
        $lengthIdentity = $this->normalizeVariantNumericIdentityValue($lengthState, $length);
        $widthIdentity = $this->normalizeVariantNumericIdentityValue($widthState, $width);
        $heightIdentity = $this->normalizeVariantNumericIdentityValue($heightState, $height);
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
        $variantAttributeMatrix = $this->buildVariantAttributeMatrix(
            sku: $sku,
            barcode: $barcode,
            size: $size,
            sizeType: $sizeType,
            packSize: $packSize,
            packSizeType: $packSizeType,
            color: $color,
            weight: $weightIdentity,
            length: $lengthIdentity,
            width: $widthIdentity,
            height: $heightIdentity,
        );
        $variantCombinationHash = $this->buildVariantCombinationHash($variantAttributeMatrix);
        $variantName = $this->buildVariantName($product, $attributes, $size, $sku);

        $baseVariantQuery = ProductVariant::query()
            ->withoutGlobalScopes()
            ->where('product_id', $product->getKey());

        $variant = null;

        if ($variantCombinationHash !== null) {
            $variant = (clone $baseVariantQuery)
                ->where('variant_combination_hash', $variantCombinationHash)
                ->first();
        }

        if (! $variant instanceof ProductVariant && $sku !== null && $variantCombinationHash === null) {
            $skuMatchQuery = (clone $baseVariantQuery)->where('sku', $sku);

            if ($barcode !== null) {
                $skuMatchQuery->where('barcode', $barcode);
            }

            if ($size !== null) {
                $skuMatchQuery->where('size', $size);
            }

            $skuMatches = $skuMatchQuery->limit(2)->get();

            if ($skuMatches->count() === 1) {
                $variant = $skuMatches->first();
            }
        }

        if (! $variant instanceof ProductVariant && $variantCombinationHash === null && $barcode !== null) {
            $variant = (clone $baseVariantQuery)
                ->where('barcode', $barcode)
                ->first();
        }

        if (! $variant instanceof ProductVariant && $variantCombinationHash === null && $sku === null && $barcode === null) {
            $fallbackQuery = (clone $baseVariantQuery)->where('name', $variantName);

            if ($size !== null) {
                $fallbackQuery->where('size', $size);
            } else {
                $fallbackQuery->whereNull('size');
            }

            $variant = $fallbackQuery->first();
        }
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
            'product_id'               => $product->getKey(),
            'name'                     => $variantName,
            'sku'                      => $sku,
            'barcode'                  => $barcode,
            'price'                    => $price,
            'cost_price'               => $costPrice,
            'stock_quantity'           => $stockQuantity,
            'weight'                   => $weight,
            'track_inventory'          => $trackInventory,
            'allow_backorder'          => $allowBackorder,
            'low_stock_threshold'      => $lowStockThreshold,
            'size'                     => $size,
            'is_enabled'               => $isEnabled,
            'attributes'               => $attributes !== [] ? $attributes : null,
            'variant_attribute_matrix' => $variantAttributeMatrix !== [] ? $variantAttributeMatrix : null,
            'variant_combination_hash' => $variantCombinationHash,
        ]);

        if (Schema::hasColumn('product_variants', 'size_type')) {
            $variant->setAttribute('size_type', $sizeType);
        }

        if (Schema::hasColumn('product_variants', 'size')) {
            $variant->setAttribute('size', $size);
        }

        if (Schema::hasColumn('product_variants', 'pack_size')) {
            $variant->setAttribute('pack_size', $packSize);
        }

        if (Schema::hasColumn('product_variants', 'pack_size_type')) {
            $variant->setAttribute('pack_size_type', $packSizeType);
        }

        if (Schema::hasColumn('product_variants', 'color')) {
            $variant->setAttribute('color', $color);
        }

        if (Schema::hasColumn('product_variants', 'length')) {
            $variant->setAttribute('length', $length);
        }

        if (Schema::hasColumn('product_variants', 'width')) {
            $variant->setAttribute('width', $width);
        }

        if (Schema::hasColumn('product_variants', 'height')) {
            $variant->setAttribute('height', $height);
        }

        $variant->save();
        $this->attachVariantToProduct($product, $variant);
        $this->syncVariantOptionAttributes($variant, [
            'color'          => $color,
            'size'           => $size,
            'size_type'      => $sizeType,
            'pack_size'      => $packSize,
            'pack_size_type' => $packSizeType,
            'weight'         => $this->toNullableString($weightState),
            'length'         => $this->toNullableString($lengthState),
            'width'          => $this->toNullableString($widthState),
            'height'         => $this->toNullableString($heightState),
        ]);

        $this->syncParentProductVariantSnapshot($product, $variant, $isNewVariant);
    }

    private function shouldCreateVariant(): bool
    {
        $sku = $this->toNullableString($this->variantStateValue('sku'));
        $barcode = $this->toNullableString($this->variantStateValue('barcode'));
        $size = $this->toNullableString($this->variantStateValue('size'));
        $sizeType = $this->toNullableString($this->variantStateValue('size_type'));
        $color = $this->toNullableString($this->variantStateValue('color'));
        $packSize = $this->toNullableString($this->variantStateValue('pack_size'));
        $packSizeType = $this->toNullableString($this->variantStateValue('pack_size_type'));
        $weight = $this->toNullableFloat($this->variantStateValue('weight'));
        $length = $this->toNullableFloat($this->variantStateValue('length'));
        $width = $this->toNullableFloat($this->variantStateValue('width'));
        $height = $this->toNullableFloat($this->variantStateValue('height'));
        $volume = $this->toNullableString($this->variantStateValue('volume'));
        $price = $this->toNullableFloat($this->variantStateValue('price'));
        $stockQuantity = $this->toNullableInteger($this->variantStateValue('stock_quantity'))
            ?? $this->toNullableInteger($this->variantStateValue('stock'));

        return $sku !== null
            || $barcode !== null
            || $size !== null
            || $sizeType !== null
            || $color !== null
            || $packSize !== null
            || $packSizeType !== null
            || $weight !== null
            || $length !== null
            || $width !== null
            || $height !== null
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
     * @return array<string, string|float>
     */
    private function buildVariantAttributeMatrix(
        ?string $sku,
        ?string $barcode,
        ?string $size,
        ?string $sizeType,
        ?string $packSize,
        ?string $packSizeType,
        ?string $color,
        string|float|null $weight,
        string|float|null $length,
        string|float|null $width,
        string|float|null $height
    ): array {
        $matrix = [];

        if ($color !== null) {
            $matrix['color'] = $color;
        }

        if ($packSizeType !== null) {
            $matrix['pack_size_type'] = $packSizeType;
        }

        if ($packSize !== null) {
            $matrix['pack_size'] = $packSize;
        }

        if ($sizeType !== null) {
            $matrix['size_type'] = $sizeType;
        }

        if ($size !== null) {
            $matrix['size'] = $size;
        }

        if ($weight !== null) {
            $matrix['weight'] = $weight;
        }

        if ($length !== null) {
            $matrix['length'] = $length;
        }

        if ($width !== null) {
            $matrix['width'] = $width;
        }

        if ($height !== null) {
            $matrix['height'] = $height;
        }

        if ($barcode !== null) {
            $matrix['barcode'] = $barcode;
        }

        if ($sku !== null) {
            $matrix['sku'] = $sku;
        }

        return $matrix;
    }

    /**
     * @param array<string, string|float> $matrix
     */
    private function buildVariantCombinationHash(array $matrix): ?string
    {
        if ($matrix === []) {
            return null;
        }

        $normalized = [];

        foreach ($matrix as $key => $value) {
            if (is_float($value)) {
                $normalized[$key] = rtrim(rtrim(number_format($value, 6, '.', ''), '0'), '.');

                continue;
            }

            $normalized[$key] = Str::lower(Str::squish($value));
        }

        ksort($normalized);

        return hash('sha256', http_build_query($normalized, '', '&', PHP_QUERY_RFC3986));
    }

    /**
     * @param array<string, string> $attributes
     */
    private function buildVariantName(Product $product, array $attributes, ?string $size, ?string $sku): string
    {
        $parts = [];

        foreach (['color', 'size_type', 'size', 'pack_size', 'pack_size_type', 'weight', 'length', 'width', 'height', 'volume', 'material'] as $field) {
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

    private function syncCategoriesForCurrentRow(Product $product): void
    {
        if (! Schema::hasTable('categories') || ! Schema::hasTable('product_categories')) {
            return;
        }

        $categories = static::resolveCategoriesFromState($this->pendingCategoriesState);

        if ($categories->isEmpty()) {
            return;
        }

        $categoryIds = $categories
            ->pluck('id')
            ->filter(static fn ($id): bool => is_numeric($id))
            ->map(static fn ($id): int => (int) $id)
            ->unique()
            ->values()
            ->all();

        if ($categoryIds === []) {
            return;
        }

        $product->categories()->syncWithoutDetaching($categoryIds);
    }

    private function syncSupplierForCurrentRow(Product $product): void
    {
        if (! Schema::hasTable('suppliers') || ! Schema::hasTable('product_supplier')) {
            return;
        }

        $supplierState = $this->variantStateValue('supplier');
        $supplier = static::resolveSupplierFromState($supplierState);

        if (! $supplier instanceof Supplier) {
            return;
        }

        $product->suppliers()->syncWithoutDetaching([$supplier->getKey()]);
    }

    /**
     * @param array<string, ?string> $optionValues
     */
    private function syncVariantOptionAttributes(ProductVariant $variant, array $optionValues): void
    {
        if (
            ! Schema::hasTable('variant_attribute_values')
            || ! Schema::hasTable('attributes')
            || ! Schema::hasTable('attribute_values')
        ) {
            return;
        }

        foreach (self::FRONTEND_OPTION_FIELDS as $field) {
            try {
                $value = $optionValues[$field] ?? null;

                if ($value === null) {
                    continue;
                }

                $attribute = $this->resolveOrCreateVariantOptionAttribute($field);

                if (! $attribute instanceof Attribute) {
                    continue;
                }

                $this->resolveOrCreateVariantOptionValue($attribute, $value);

                $payload = [
                    'attribute_name'  => $attribute->name,
                    'attribute_value' => $value,
                ];

                if (Schema::hasColumn('variant_attribute_values', 'attribute_value_display')) {
                    $payload['attribute_value_display'] = $value;
                }

                if (Schema::hasColumn('variant_attribute_values', 'attribute_value_lt')) {
                    $payload['attribute_value_lt'] = $value;
                }

                if (Schema::hasColumn('variant_attribute_values', 'attribute_value_en')) {
                    $payload['attribute_value_en'] = $value;
                }

                if (Schema::hasColumn('variant_attribute_values', 'attribute_value_slug')) {
                    $payload['attribute_value_slug'] = $this->normalizeAttributeValueSlug($value);
                }

                if (Schema::hasColumn('variant_attribute_values', 'sort_order')) {
                    $payload['sort_order'] = array_search($field, self::FRONTEND_OPTION_FIELDS, true) ?: 0;
                }

                if (Schema::hasColumn('variant_attribute_values', 'is_filterable')) {
                    $payload['is_filterable'] = true;
                }

                if (Schema::hasColumn('variant_attribute_values', 'is_searchable')) {
                    $payload['is_searchable'] = true;
                }

                VariantAttributeValue::query()
                    ->withoutGlobalScopes()
                    ->updateOrCreate(
                        [
                            'variant_id'   => $variant->getKey(),
                            'attribute_id' => $attribute->getKey(),
                        ],
                        $payload,
                    );
            } catch (Throwable $exception) {
                // Best-effort enrichment: attribute sync errors should not fail the product row import.
                report($exception);

                continue;
            }
        }
    }

    private function resolveOrCreateVariantOptionAttribute(string $field): ?Attribute
    {
        $query = Attribute::query()->withoutGlobalScopes();

        if (Schema::hasColumn('attributes', 'slug')) {
            $query->where('slug', $field);
        } else {
            $query->where('name', Str::headline($field));
        }

        $attribute = $query->first();

        if ($attribute instanceof Attribute) {
            return $attribute;
        }

        $createPayload = $this->filterExistingTableColumns('attributes', [
            'name'          => Str::headline($field),
            'slug'          => $field,
            'type'          => 'select',
            'is_required'   => false,
            'is_filterable' => true,
            'is_searchable' => true,
            'is_visible'    => true,
            'is_editable'   => true,
            'is_sortable'   => true,
            'is_enabled'    => true,
            'is_active'     => true,
            'sort_order'    => 0,
        ]);

        if (! array_key_exists('name', $createPayload)) {
            return null;
        }

        return Attribute::query()
            ->withoutGlobalScopes()
            ->create($createPayload);
    }

    private function resolveOrCreateVariantOptionValue(Attribute $attribute, string $value): ?AttributeValue
    {
        $slug = $this->normalizeAttributeValueSlug($value);

        $lookupQuery = AttributeValue::query()
            ->withoutGlobalScopes()
            ->where('attribute_id', $attribute->getKey());

        if (Schema::hasColumn('attribute_values', 'slug')) {
            $lookupQuery->where('slug', $slug);
        } else {
            $lookupQuery->where('value', $value);
        }

        $attributeValue = $lookupQuery->first();

        if (! $attributeValue instanceof AttributeValue) {
            $createPayload = $this->filterExistingTableColumns('attribute_values', [
                'attribute_id'         => $attribute->getKey(),
                'value'                => $value,
                'display_value'        => $value,
                'slug'                 => $slug,
                'is_enabled'           => true,
                'is_active'            => true,
                'is_searchable'        => true,
                'sort_order'           => 0,
                'is_default'           => false,
                'hex_color'            => null,
                'metadata'             => null,
                'color_code'           => null,
                'attribute_value_type' => 'text',
                'valueable_type'       => null,
                'valueable_id'         => null,
                'image'                => null,
                'description'          => null,
            ]);

            if (! array_key_exists('attribute_id', $createPayload) || ! array_key_exists('value', $createPayload)) {
                return null;
            }

            $attributeValue = AttributeValue::query()
                ->withoutGlobalScopes()
                ->create($createPayload);
        }

        if ($attributeValue->value !== $value || $attributeValue->display_value !== $value) {
            $attributeValue->forceFill([
                'value'         => $value,
                'display_value' => $value,
            ])->saveQuietly();
        }

        return $attributeValue;
    }

    private function normalizeAttributeValueSlug(string $value): string
    {
        $slug = Str::slug($value);

        if ($slug !== '') {
            return $slug;
        }

        $asciiSlug = Str::slug(Str::ascii($value));

        return $asciiSlug !== '' ? $asciiSlug : Str::lower(Str::random(12));
    }

    private function normalizeVariantNumericIdentityValue(mixed $rawValue, ?float $numericValue): string|float|null
    {
        if ($numericValue !== null) {
            return $numericValue;
        }

        $rawString = $this->toNullableString($rawValue);

        if ($rawString === null) {
            return null;
        }

        $normalizedNumericString = str_replace(',', '.', $rawString);

        if (is_numeric($normalizedNumericString)) {
            return (float) $normalizedNumericString;
        }

        return Str::squish($rawString);
    }

    /**
     * @param  array<string, mixed> $values
     * @return array<string, mixed>
     */
    private function filterExistingTableColumns(string $table, array $values): array
    {
        $filtered = [];

        foreach ($values as $column => $value) {
            if (! Schema::hasColumn($table, $column)) {
                continue;
            }

            $filtered[$column] = $value;
        }

        return $filtered;
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
        return self::toNullableLocalizedFloat($value);
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

    private static function normalizeImportedNumericState(mixed $originalState, mixed $state): ?float
    {
        $normalizedFromOriginal = self::toNullableLocalizedFloat($originalState);

        if ($normalizedFromOriginal !== null) {
            return $normalizedFromOriginal;
        }

        return self::toNullableLocalizedFloat($state);
    }

    private static function toNullableLocalizedFloat(mixed $value): ?float
    {
        if (is_int($value) || is_float($value)) {
            return (float) $value;
        }

        if (! is_string($value)) {
            return null;
        }

        $normalized = trim($value);

        if ($normalized === '') {
            return null;
        }

        $normalized = str_replace("\xc2\xa0", ' ', $normalized);
        $normalized = preg_replace('/\s+/u', '', $normalized) ?? $normalized;

        if (str_contains($normalized, ',') && str_contains($normalized, '.')) {
            $lastComma = strrpos($normalized, ',');
            $lastDot = strrpos($normalized, '.');

            if ($lastComma !== false && $lastDot !== false && $lastComma > $lastDot) {
                $normalized = str_replace('.', '', $normalized);
                $normalized = str_replace(',', '.', $normalized);
            } else {
                $normalized = str_replace(',', '', $normalized);
            }
        } elseif (str_contains($normalized, ',')) {
            $isDecimalComma = preg_match('/^-?\d+,\d+$/', $normalized) === 1;
            $normalized = $isDecimalComma
                ? str_replace(',', '.', $normalized)
                : str_replace(',', '', $normalized);
        }

        $normalized = preg_replace('/[^0-9.\-]/', '', $normalized) ?? $normalized;

        if ($normalized === '' || $normalized === '-' || $normalized === '.' || $normalized === '-.') {
            return null;
        }

        if (! is_numeric($normalized)) {
            return null;
        }

        return (float) $normalized;
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

    private static function resolveSupplierFromState(mixed $state): ?Supplier
    {
        if ($state === null) {
            return null;
        }

        $raw = is_string($state) ? trim($state) : $state;

        if ($raw === '' || $raw === null) {
            return null;
        }

        if (is_numeric($raw)) {
            $supplier = Supplier::query()->find((int) $raw);

            if ($supplier instanceof Supplier) {
                return $supplier;
            }
        }

        $name = is_string($raw) ? trim($raw) : trim((string) $raw);

        if ($name === '') {
            return null;
        }

        $normalizedName = Str::lower(Str::squish($name));

        $supplier = Supplier::query()
            ->whereRaw('LOWER(TRIM(name)) = ?', [$normalizedName])
            ->first();

        if ($supplier instanceof Supplier) {
            return $supplier;
        }

        $companyCode = Str::upper(Str::slug($name, ''));

        if ($companyCode === '') {
            $companyCode = 'SUPPLIER';
        }

        return Supplier::query()->create([
            'name'         => $name,
            'company_code' => $companyCode,
            'code'         => Str::slug($name),
            'is_enabled'   => true,
        ]);
    }

    private static function resolveCategoriesFromState(mixed $state): EloquentCollection
    {
        $items = is_array($state) ? $state : [$state];

        $names = collect($items)
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
            $path = $this->downloadAndStoreProductImageFromUrl($this->record, $imageUrl);

            if ($path === null) {
                return;
            }

            $this->imageWriteService()->replaceWithPath($this->record, $path, $this->record->name);
        } catch (Throwable) {
            // Best-effort image import. Ignore errors so the product row still imports successfully.
            return;
        }
    }

    private function downloadAndStoreProductImageFromUrl(Product $product, string $imageUrl): ?string
    {
        try {
            $contents = $this->downloadImageContents($imageUrl);

            // Don't fail the row if the image can't be downloaded: import the product without an image.
            if ($contents === null) {
                return null;
            }

            $sourceExtension = $this->resolveImageExtension($imageUrl, $contents['content_type'] ?? null);
            $converted = $this->convertAndResizeImageContents($contents['body'], $sourceExtension);
            $path = 'product-images/' . $product->getKey() . '/import-' . Str::uuid() . '.' . $converted['extension'];

            Storage::disk('public')->put($path, $converted['body']);

            return $path;
        } catch (Throwable) {
            return null;
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
                str_contains($normalized, 'avif') => 'avif',
                str_contains($normalized, 'svg')  => 'svg',
                str_contains($normalized, 'bmp')  => 'bmp',
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

        if (! in_array($extension, ['jpg', 'jpeg', 'png', 'webp', 'gif', 'avif', 'svg', 'bmp'], true)) {
            return 'jpg';
        }

        return $extension === 'jpeg' ? 'jpg' : $extension;
    }

    /**
     * @return array{body:string, extension:string}
     */
    private function convertAndResizeImageContents(string $imageContents, string $sourceExtension): array
    {
        $normalizedSourceExtension = $this->normalizeImageExtension($sourceExtension);

        if (! function_exists('imagecreatefromstring')) {
            return [
                'body'      => $imageContents,
                'extension' => $normalizedSourceExtension,
            ];
        }

        $sourceImage = @imagecreatefromstring($imageContents);

        if ($sourceImage === false) {
            return [
                'body'      => $imageContents,
                'extension' => $normalizedSourceExtension,
            ];
        }

        $targetExtension = $this->resolveTargetImageExtension($normalizedSourceExtension);
        $convertedBody = $this->resizeImageContents($imageContents, $targetExtension);
        imagedestroy($sourceImage);

        if ($convertedBody === '') {
            return [
                'body'      => $imageContents,
                'extension' => $normalizedSourceExtension,
            ];
        }

        return [
            'body'      => $convertedBody,
            'extension' => $targetExtension,
        ];
    }

    private function resolveTargetImageExtension(string $sourceExtension): string
    {
        if ($sourceExtension === 'svg') {
            return 'svg';
        }

        if ($sourceExtension === 'gif') {
            return 'gif';
        }

        if (function_exists('imagewebp')) {
            return 'webp';
        }

        return match ($sourceExtension) {
            'png'   => 'png',
            'bmp'   => function_exists('imagebmp') ? 'bmp' : 'jpg',
            default => 'jpg',
        };
    }

    private function normalizeImageExtension(string $extension): string
    {
        $normalized = Str::lower(trim($extension));

        return match ($normalized) {
            'jpeg'  => 'jpg',
            'tif'   => 'tiff',
            default => $normalized !== '' ? $normalized : 'jpg',
        };
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
            'bmp'   => function_exists('imagebmp') ? imagebmp($targetImage) : imagejpeg($targetImage, null, self::IMPORT_IMAGE_QUALITY),
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
