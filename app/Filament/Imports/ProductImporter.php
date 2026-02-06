<?php

declare(strict_types=1);

namespace App\Filament\Imports;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
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
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Number;
use Illuminate\Support\Str;
use Throwable;

class ProductImporter extends BaseImporter
{
    protected static ?string $model = Product::class;

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
            ImportColumn::make('description'),
            ImportColumn::make('short_description'),
            ImportColumn::make('sku')
                ->label('SKU')
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
            ImportColumn::make('seo_title'),
            ImportColumn::make('seo_description'),
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
                ->label('Image URL')
                ->ignoreBlankState()
                ->rules(['nullable', 'url:http,https']),
        ];
    }

    protected function afterSave(): void
    {
        $imageUrl = $this->data['image_url'] ?? null;

        if (! is_string($imageUrl) || trim($imageUrl) === '') {
            return;
        }

        $this->replaceProductImageFromUrl(trim($imageUrl));
    }

    public function resolveRecord(): Product
    {
        if ($this->shouldSync() && ($syncRecord = $this->resolveRecordFromSyncKeys())) {
            return $syncRecord;
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
            'SEO & Metadata' => [
                'seo_title',
                'seo_description',
                'meta_title',
                'meta_description',
                'meta_keywords',
                'tags',
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
                'available_from',
                'available_until',
            ],
        ];
    }

    private function shouldSync(): bool
    {
        return (bool) ($this->options['should_sync'] ?? false);
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
            $slug = Str::slug($name);

            $category = Category::query()
                ->withoutGlobalScopes()
                ->where('slug', $slug)
                ->first()
                ?? Category::query()
                    ->withoutGlobalScopes()
                    ->where('name', $name)
                    ->first();

            if (! $category) {
                $category = Category::query()->create([
                    'name'         => $name,
                    'slug'         => $slug,
                    'sort_order'   => 0,
                    'is_enabled'   => true,
                    'is_active'    => true,
                    'is_visible'   => true,
                    'is_featured'  => false,
                    'show_in_menu' => true,
                ]);
            }

            $categories->push($category);
        }

        return $categories;
    }

    private function replaceProductImageFromUrl(string $imageUrl): void
    {
        if (! $this->record instanceof Product || ! $this->record->exists) {
            return;
        }

        $contents = $this->downloadImageContents($imageUrl);

        if ($contents === null) {
            throw new RowImportFailedException('Image download failed.');
        }

        $extension = $this->resolveImageExtension($imageUrl, $contents['content_type'] ?? null);
        $path = 'product-images/' . $this->record->getKey() . '/import-' . Str::uuid() . '.' . $extension;

        Storage::disk('public')->put($path, $contents['body']);

        $existingImages = $this->record
            ->images()
            ->withoutGlobalScopes()
            ->get();

        foreach ($existingImages as $existingImage) {
            Storage::disk('public')->delete((string) $existingImage->path);
        }

        $this->record->images()->withoutGlobalScopes()->delete();

        ProductImage::query()->create([
            'product_id' => $this->record->getKey(),
            'path'       => $path,
            'alt_text'   => $this->record->name,
            'sort_order' => 0,
            'is_default' => true,
            'is_active'  => true,
        ]);
    }

    /**
     * @return array{body:string, content_type:string|null}|null
     */
    private function downloadImageContents(string $imageUrl): ?array
    {
        try {
            $response = Http::timeout(20)
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
}
