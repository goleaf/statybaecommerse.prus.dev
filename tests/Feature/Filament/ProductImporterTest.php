<?php

declare(strict_types=1);

use App\Filament\Imports\ProductImporter;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductVariant;
use App\Models\Supplier;
use App\Models\User;
use Filament\Actions\Imports\Jobs\ImportCsv;
use Filament\Actions\Imports\Models\FailedImportRow;
use Filament\Actions\Imports\Models\Import;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

function productImporterFixturePng(): string
{
    $upload = UploadedFile::fake()->image('remote.png', 16, 16);
    $contents = file_get_contents((string) $upload->getRealPath());

    return is_string($contents) ? $contents : '';
}

it('imports a fully mapped product row with blank optional fields', function (): void {
    $user = User::factory()->admin()->create();

    $import = new Import;
    $import->user()->associate($user);
    $import->file_name = 'product-import.csv';
    $import->file_path = base_path('storage/imports/product-import.csv');
    $import->importer = ProductImporter::class;
    $import->total_rows = 1;
    $import->save();

    $columns = collect(ProductImporter::getColumns())->map->getName()->values();
    $row = $columns->mapWithKeys(fn (string $name) => [$name => ''])->all();
    $row['name'] = 'Test Product';
    $row['price'] = '6.0';
    $row['sku'] = 'SKU-TEST-1';

    $columnMap = $columns->mapWithKeys(fn (string $name) => [$name => $name])->all();

    (new ImportCsv($import, [$row], $columnMap, []))->handle();

    $import->refresh();

    expect($import->successful_rows)->toBe(1)
        ->and($import->failedRows)->toHaveCount(0);

    $product = Product::withoutGlobalScopes()->first();

    expect($product)->not->toBeNull()
        ->and($product->name)->toBe('Test Product')
        ->and($product->slug)->toBe(Str::slug('Test Product'))
        ->and((float) $product->price)->toBe(6.0)
        ->and($product->status)->toBe('published')
        ->and($product->is_enabled)->toBeTrue()
        ->and($product->published_at)->not->toBeNull();

    expect(Product::query()->count())->toBe(1);
});

it('backfills published_at for existing published products when import row leaves it blank', function (): void {
    $user = User::factory()->admin()->create();

    $product = Product::factory()->create([
        'name'         => 'Visibility Backfill Product',
        'sku'          => 'VIS-BACKFILL-001',
        'status'       => 'published',
        'is_enabled'   => true,
        'published_at' => null,
    ]);

    $import = new Import;
    $import->user()->associate($user);
    $import->file_name = 'product-import.csv';
    $import->file_path = base_path('storage/imports/product-import.csv');
    $import->importer = ProductImporter::class;
    $import->total_rows = 1;
    $import->save();

    $columns = collect(ProductImporter::getColumns())->map->getName()->values();
    $row = $columns->mapWithKeys(fn (string $name) => [$name => ''])->all();
    $row['name'] = 'Visibility Backfill Product';
    $row['sku'] = 'VIS-BACKFILL-001';
    $row['price'] = '19.99';
    $row['status'] = 'published';
    $row['published_at'] = '';

    $columnMap = $columns->mapWithKeys(fn (string $name) => [$name => $name])->all();
    $options = [
        'should_sync' => true,
        'sync_keys'   => [
            ['field' => 'sku'],
        ],
    ];

    (new ImportCsv($import, [$row], $columnMap, $options))->handle();

    $product->refresh();

    expect($product->published_at)->not->toBeNull()
        ->and($product->isPublished())->toBeTrue();
});

it('creates missing brand and categories during product import', function (): void {
    $user = User::factory()->admin()->create();

    $import = new Import;
    $import->user()->associate($user);
    $import->file_name = 'product-import.csv';
    $import->file_path = base_path('storage/imports/product-import.csv');
    $import->importer = ProductImporter::class;
    $import->total_rows = 1;
    $import->save();

    $columns = collect(ProductImporter::getColumns())->map->getName()->values();
    $row = $columns->mapWithKeys(fn (string $name) => [$name => ''])->all();
    $row['name'] = 'Category Test Product';
    $row['price'] = '12.50';
    $row['sku'] = 'SKU-CAT-001';
    $row['brand'] = 'Acme Tools';
    $row['categories'] = 'Tools; Hardware';

    $columnMap = $columns->mapWithKeys(fn (string $name) => [$name => $name])->all();

    (new ImportCsv($import, [$row], $columnMap, []))->handle();

    $brand = Brand::withoutGlobalScopes()->firstWhere('name', 'Acme Tools');
    $categories = Category::withoutGlobalScopes()
        ->whereIn('name', ['Tools', 'Hardware'])
        ->get();

    $product = Product::withoutGlobalScopes()->first();
    $productCategoryNames = $product?->categories()
        ->withoutGlobalScopes()
        ->pluck('name')
        ->all() ?? [];

    expect($brand)->not->toBeNull()
        ->and($categories)->toHaveCount(2)
        ->and($product)->not->toBeNull()
        ->and($product->brand_id)->toBe($brand->getKey())
        ->and($productCategoryNames)->toContain('Tools', 'Hardware');
});

it('creates missing suppliers and reuses existing suppliers during product import', function (): void {
    $user = User::factory()->admin()->create();
    $existingSupplier = Supplier::factory()->create([
        'name' => 'Baltic Supply',
    ]);

    $import = new Import;
    $import->user()->associate($user);
    $import->file_name = 'product-import.csv';
    $import->file_path = base_path('storage/imports/product-import.csv');
    $import->importer = ProductImporter::class;
    $import->total_rows = 2;
    $import->save();

    $columns = collect(ProductImporter::getColumns())->map->getName()->values();
    $baseRow = $columns->mapWithKeys(fn (string $name) => [$name => ''])->all();

    $rows = [
        array_merge($baseRow, [
            'name'     => 'Supplier Reuse Product',
            'sku'      => 'SUP-REUSE-001',
            'price'    => '10.00',
            'supplier' => '  baltic supply  ',
        ]),
        array_merge($baseRow, [
            'name'     => 'Supplier Create Product',
            'sku'      => 'SUP-CREATE-001',
            'price'    => '15.00',
            'supplier' => 'Fresh Vendor',
        ]),
    ];

    $columnMap = $columns->mapWithKeys(fn (string $name) => [$name => $name])->all();

    (new ImportCsv($import, $rows, $columnMap, []))->handle();

    $reuseProduct = Product::withoutGlobalScopes()->firstWhere('sku', 'SUP-REUSE-001');
    $createProduct = Product::withoutGlobalScopes()->firstWhere('sku', 'SUP-CREATE-001');
    $newSupplier = Supplier::query()->where('name', 'Fresh Vendor')->first();

    expect(Supplier::query()->count())->toBe(2)
        ->and($newSupplier)->not->toBeNull()
        ->and($newSupplier?->company_code)->not->toBeNull()
        ->and($newSupplier?->code)->toBe('fresh-vendor')
        ->and($reuseProduct)->not->toBeNull()
        ->and($createProduct)->not->toBeNull()
        ->and($reuseProduct?->suppliers()->pluck('suppliers.id')->all())->toContain($existingSupplier->getKey())
        ->and($createProduct?->suppliers()->pluck('suppliers.id')->all())->toContain($newSupplier?->getKey());
});

it('upserts products when sync mode matches by sku', function (): void {
    $user = User::factory()->admin()->create();
    $existing = Product::factory()->create([
        'sku'  => 'SYNC-001',
        'name' => 'Old Name',
    ]);

    $import = new Import;
    $import->user()->associate($user);
    $import->file_name = 'product-import.csv';
    $import->file_path = base_path('storage/imports/product-import.csv');
    $import->importer = ProductImporter::class;
    $import->total_rows = 1;
    $import->save();

    $columns = collect(ProductImporter::getColumns())->map->getName()->values();
    $row = $columns->mapWithKeys(fn (string $name) => [$name => ''])->all();
    $row['name'] = 'Updated Name';
    $row['sku'] = 'SYNC-001';

    $columnMap = $columns->mapWithKeys(fn (string $name) => [$name => $name])->all();
    $options = [
        'should_sync' => true,
        'sync_keys'   => [
            ['field' => 'sku'],
        ],
    ];

    (new ImportCsv($import, [$row], $columnMap, $options))->handle();

    $import->refresh();
    $existing->refresh();

    expect($import->successful_rows)->toBe(1)
        ->and(Product::withoutGlobalScopes()->count())->toBe(1)
        ->and($existing->name)->toBe('Updated Name');
});

it('falls back to the next sync key when the first one is blank', function (): void {
    $user = User::factory()->admin()->create();
    $existing = Product::factory()->create([
        'sku'     => 'SYNC-002',
        'barcode' => 'BAR-002',
        'name'    => 'Old Barcode Name',
    ]);

    $import = new Import;
    $import->user()->associate($user);
    $import->file_name = 'product-import.csv';
    $import->file_path = base_path('storage/imports/product-import.csv');
    $import->importer = ProductImporter::class;
    $import->total_rows = 1;
    $import->save();

    $columns = collect(ProductImporter::getColumns())->map->getName()->values();
    $row = $columns->mapWithKeys(fn (string $name) => [$name => ''])->all();
    $row['name'] = 'Updated Barcode Name';
    $row['barcode'] = 'BAR-002';

    $columnMap = $columns->mapWithKeys(fn (string $name) => [$name => $name])->all();
    $options = [
        'should_sync' => true,
        'sync_keys'   => [
            ['field' => 'sku'],
            ['field' => 'barcode'],
        ],
    ];

    (new ImportCsv($import, [$row], $columnMap, $options))->handle();

    $existing->refresh();

    expect($existing->name)->toBe('Updated Barcode Name');
});

it('fails the row when a sync key matches multiple products', function (): void {
    $user = User::factory()->admin()->create();

    Product::factory()->create([
        'name' => 'Duplicate Name',
        'sku'  => 'DUP-1',
    ]);
    Product::factory()->create([
        'name' => 'Duplicate Name',
        'sku'  => 'DUP-2',
    ]);

    $import = new Import;
    $import->user()->associate($user);
    $import->file_name = 'product-import.csv';
    $import->file_path = base_path('storage/imports/product-import.csv');
    $import->importer = ProductImporter::class;
    $import->total_rows = 1;
    $import->save();

    $columns = collect(ProductImporter::getColumns())->map->getName()->values();
    $row = $columns->mapWithKeys(fn (string $name) => [$name => ''])->all();
    $row['name'] = 'Duplicate Name';
    $row['sku'] = 'DUP-NEW';

    $columnMap = $columns->mapWithKeys(fn (string $name) => [$name => $name])->all();
    $options = [
        'should_sync' => true,
        'sync_keys'   => [
            ['field' => 'name'],
        ],
    ];

    (new ImportCsv($import, [$row], $columnMap, $options))->handle();

    $import->refresh();

    expect($import->successful_rows)->toBe(0)
        ->and(FailedImportRow::query()->where('import_id', $import->getKey())->count())->toBe(1);
});

it('downloads image_url and keeps exactly one image for the product', function (): void {
    Storage::fake('public');
    Http::fake([
        'https://example.com/images/product.png' => Http::response(productImporterFixturePng(), 200, [
            'Content-Type' => 'image/png',
        ]),
    ]);

    $user = User::factory()->admin()->create();

    $existing = Product::factory()->create([
        'sku'  => 'IMG-SYNC-001',
        'name' => 'Existing Product',
    ]);

    ProductImage::factory()->create([
        'product_id' => $existing->getKey(),
        'path'       => 'product-images/legacy-1.jpg',
        'sort_order' => 1,
    ]);
    ProductImage::factory()->create([
        'product_id' => $existing->getKey(),
        'path'       => 'product-images/legacy-2.jpg',
        'sort_order' => 2,
    ]);

    $import = new Import;
    $import->user()->associate($user);
    $import->file_name = 'product-import.csv';
    $import->file_path = base_path('storage/imports/product-import.csv');
    $import->importer = ProductImporter::class;
    $import->total_rows = 1;
    $import->save();

    $columns = collect(ProductImporter::getColumns())->map->getName()->values();
    $row = $columns->mapWithKeys(fn (string $name) => [$name => ''])->all();
    $row['name'] = 'Product With Imported Image';
    $row['sku'] = 'IMG-SYNC-001';
    $row['image_url'] = 'https://example.com/images/product.png';

    $columnMap = $columns->mapWithKeys(fn (string $name) => [$name => $name])->all();
    $options = [
        'should_sync' => true,
        'sync_keys'   => [
            ['field' => 'sku'],
        ],
    ];

    (new ImportCsv($import, [$row], $columnMap, $options))->handle();

    $import->refresh();
    $existing->refresh();

    $images = $existing->images()->withoutGlobalScopes()->get();

    expect($import->successful_rows)->toBe(1)
        ->and($images)->toHaveCount(1)
        ->and($images->first()?->is_default)->toBeTrue();

    $storedPath = (string) $images->first()?->path;

    expect($storedPath)->toStartWith('product-images/' . $existing->getKey() . '/')
        ->and($storedPath)->toMatch('/\.(png|webp|jpg)$/');

    Storage::disk('public')->assertExists($storedPath);

    if (Schema::hasTable('media')) {
        expect($existing->getFirstMedia('thumbnail'))->not->toBeNull()
            ->and($existing->getMedia('product_images'))->toHaveCount(1);
    }
});

it('skips image import when image_url download fails and still imports the product', function (): void {
    Storage::fake('public');
    Http::fake([
        'https://example.com/images/missing.png' => Http::response('', 404),
    ]);

    $user = User::factory()->admin()->create();

    $import = new Import;
    $import->user()->associate($user);
    $import->file_name = 'product-import.csv';
    $import->file_path = base_path('storage/imports/product-import.csv');
    $import->importer = ProductImporter::class;
    $import->total_rows = 1;
    $import->save();

    $columns = collect(ProductImporter::getColumns())->map->getName()->values();
    $row = $columns->mapWithKeys(fn (string $name) => [$name => ''])->all();
    $row['name'] = 'Product Without Image';
    $row['sku'] = 'IMG-FAIL-001';
    $row['image_url'] = 'https://example.com/images/missing.png';

    $columnMap = $columns->mapWithKeys(fn (string $name) => [$name => $name])->all();

    (new ImportCsv($import, [$row], $columnMap, []))->handle();

    $import->refresh();

    expect($import->successful_rows)->toBe(1)
        ->and($import->failedRows)->toHaveCount(0);

    $product = Product::withoutGlobalScopes()->firstWhere('sku', 'IMG-FAIL-001');

    expect($product)->not->toBeNull();

    $images = $product?->images()->withoutGlobalScopes()->get();

    expect($images)->toHaveCount(0);
});

it('appends image paths from image column without replacing existing images', function (): void {
    Storage::fake('public');
    Http::fake([
        'https://example.com/images/appended-image.jpg' => Http::response(productImporterFixturePng(), 200, [
            'Content-Type' => 'image/png',
        ]),
    ]);
    $user = User::factory()->admin()->create();

    $existing = Product::factory()->create([
        'sku'  => 'IMG-APPEND-001',
        'name' => 'Append Existing Product',
    ]);

    ProductImage::factory()->create([
        'product_id' => $existing->getKey(),
        'path'       => 'product-images/existing-append.jpg',
        'sort_order' => 0,
        'is_default' => true,
    ]);

    $import = new Import;
    $import->user()->associate($user);
    $import->file_name = 'product-import.csv';
    $import->file_path = base_path('storage/imports/product-import.csv');
    $import->importer = ProductImporter::class;
    $import->total_rows = 1;
    $import->save();

    $columns = collect(ProductImporter::getColumns())->map->getName()->values();
    $row = $columns->mapWithKeys(fn (string $name) => [$name => ''])->all();
    $row['name'] = 'Append Existing Product';
    $row['sku'] = 'IMG-APPEND-001';
    $row['image'] = 'https://example.com/images/appended-image.jpg';

    $columnMap = $columns->mapWithKeys(fn (string $name) => [$name => $name])->all();
    $options = [
        'should_sync' => true,
        'sync_keys'   => [
            ['field' => 'sku'],
        ],
    ];

    (new ImportCsv($import, [$row], $columnMap, $options))->handle();

    $existing->refresh();
    $images = $existing->images()->withoutGlobalScopes()->orderBy('sort_order')->get();
    $downloadedImage = $images->firstWhere('path', '!=', 'product-images/existing-append.jpg');

    expect($import->fresh()->successful_rows)->toBe(1)
        ->and($images)->toHaveCount(2)
        ->and($images->pluck('path')->all())->toContain('product-images/existing-append.jpg')
        ->and($downloadedImage)->not->toBeNull()
        ->and((string) $downloadedImage?->path)->toStartWith('product-images/' . $existing->getKey() . '/')
        ->and((string) $downloadedImage?->path)->not->toStartWith('http');

    Storage::disk('public')->assertExists((string) $downloadedImage?->path);
});

it('replaces image_url and appends image column paths in the same import row', function (): void {
    Storage::fake('public');
    Http::fake([
        'https://example.com/images/combined.png' => Http::response(productImporterFixturePng(), 200, [
            'Content-Type' => 'image/png',
        ]),
        'https://example.com/images/combined-extra.jpg' => Http::response(productImporterFixturePng(), 200, [
            'Content-Type' => 'image/png',
        ]),
    ]);

    $user = User::factory()->admin()->create();
    $existing = Product::factory()->create([
        'sku'  => 'IMG-COMBINED-001',
        'name' => 'Combined Product',
    ]);

    ProductImage::factory()->create([
        'product_id' => $existing->getKey(),
        'path'       => 'product-images/legacy-combined.jpg',
        'sort_order' => 0,
        'is_default' => true,
    ]);

    $import = new Import;
    $import->user()->associate($user);
    $import->file_name = 'product-import.csv';
    $import->file_path = base_path('storage/imports/product-import.csv');
    $import->importer = ProductImporter::class;
    $import->total_rows = 1;
    $import->save();

    $columns = collect(ProductImporter::getColumns())->map->getName()->values();
    $row = $columns->mapWithKeys(fn (string $name) => [$name => ''])->all();
    $row['name'] = 'Combined Product';
    $row['sku'] = 'IMG-COMBINED-001';
    $row['image_url'] = 'https://example.com/images/combined.png';
    $row['image'] = 'https://example.com/images/combined-extra.jpg';

    $columnMap = $columns->mapWithKeys(fn (string $name) => [$name => $name])->all();
    $options = [
        'should_sync' => true,
        'sync_keys'   => [
            ['field' => 'sku'],
        ],
    ];

    (new ImportCsv($import, [$row], $columnMap, $options))->handle();

    $existing->refresh();
    $images = $existing->images()->withoutGlobalScopes()->orderBy('sort_order')->get();
    $storedPaths = $images->pluck('path')->all();

    expect($import->fresh()->successful_rows)->toBe(1)
        ->and($images)->toHaveCount(2)
        ->and(collect($storedPaths)->contains(fn (mixed $path): bool => is_string($path) && str_starts_with($path, 'http')))->toBeFalse();

    foreach ($storedPaths as $storedPath) {
        expect((string) $storedPath)->toStartWith('product-images/' . $existing->getKey() . '/');
        Storage::disk('public')->assertExists((string) $storedPath);
    }
});

it('groups same-name rows under one product and creates multiple variants', function (): void {
    $user = User::factory()->admin()->create();

    $import = new Import;
    $import->user()->associate($user);
    $import->file_name = 'product-import.csv';
    $import->file_path = base_path('storage/imports/product-import.csv');
    $import->importer = ProductImporter::class;
    $import->total_rows = 3;
    $import->save();

    $columns = collect(ProductImporter::getColumns())->map->getName()->values();
    $baseRow = $columns->mapWithKeys(fn (string $name) => [$name => ''])->all();

    $rows = [
        array_merge($baseRow, [
            'name'           => 'Beton M-150',
            'sku'            => 'BET-M150-20',
            'barcode'        => 'BET-M150-20-BAR',
            'brand'          => 'Acme Build',
            'price'          => '12.50',
            'stock_quantity' => '100',
            'weight'         => '20',
            'length'         => '40',
            'width'          => '30',
            'height'         => '10',
            'size'           => '20kg',
            'size_type'      => 'Bag',
            'pack_size'      => '20',
            'pack_size_type' => 'kg',
            'color'          => 'Gray',
            'description'    => 'Betonas',
        ]),
        array_merge($baseRow, [
            'name'           => 'Beton M-150',
            'sku'            => 'BET-M150-40',
            'barcode'        => 'BET-M150-40-BAR',
            'brand'          => 'Acme Build',
            'price'          => '22.00',
            'stock_quantity' => '50',
            'weight'         => '40',
            'length'         => '50',
            'width'          => '35',
            'height'         => '12',
            'size'           => '40kg',
            'size_type'      => 'Bag',
            'pack_size'      => '40',
            'pack_size_type' => 'kg',
            'color'          => 'Gray',
            'description'    => 'Betonas',
        ]),
        array_merge($baseRow, [
            'name'           => 'Dažai Dulux',
            'sku'            => 'DAZ-001-R',
            'barcode'        => 'DAZ-001-R-BAR',
            'brand'          => 'Dulux',
            'price'          => '8.99',
            'stock_quantity' => '200',
            'weight'         => '1.2',
            'length'         => '11',
            'width'          => '11',
            'height'         => '20',
            'color'          => 'Red',
            'pack_size'      => '1L',
            'pack_size_type' => 'l',
            'size_type'      => 'Can',
        ]),
    ];

    $columnMap = $columns->mapWithKeys(fn (string $name) => [$name => $name])->all();

    (new ImportCsv($import, $rows, $columnMap, []))->handle();

    $import->refresh();

    expect($import->successful_rows)->toBe(3)
        ->and($import->failedRows)->toHaveCount(0)
        ->and(Product::withoutGlobalScopes()->count())->toBe(2);

    $beton = Product::withoutGlobalScopes()->where('name', 'Beton M-150')->first();
    $dulux = Product::withoutGlobalScopes()->where('name', 'Dažai Dulux')->first();

    expect($beton)->not->toBeNull()
        ->and($dulux)->not->toBeNull();

    $betonVariants = $beton?->variants()->withoutGlobalScopes()->orderBy('sku')->get();
    $duluxVariants = $dulux?->variants()->withoutGlobalScopes()->get();

    expect($betonVariants)->toHaveCount(2)
        ->and($betonVariants?->pluck('sku')->all())->toBe(['BET-M150-20', 'BET-M150-40'])
        ->and((float) ($beton?->price ?? 0))->toBe(12.5);

    $betonPrimaryVariant = $betonVariants?->firstWhere('sku', 'BET-M150-20');

    expect($betonPrimaryVariant)->not->toBeNull()
        ->and($betonPrimaryVariant?->barcode)->toBe('BET-M150-20-BAR')
        ->and($betonPrimaryVariant?->size)->toBe('20kg')
        ->and((float) ($betonPrimaryVariant?->price ?? 0))->toBe(12.5)
        ->and((float) ($betonPrimaryVariant?->weight ?? 0))->toBe(20.0)
        ->and($betonPrimaryVariant?->attributes['size_type'] ?? null)->toBe('Bag')
        ->and($betonPrimaryVariant?->attributes['pack_size'] ?? null)->toBe('20')
        ->and($betonPrimaryVariant?->attributes['pack_size_type'] ?? null)->toBe('kg')
        ->and($betonPrimaryVariant?->attributes['color'] ?? null)->toBe('Gray')
        ->and($betonPrimaryVariant?->attributes['weight'] ?? null)->toBe('20')
        ->and($betonPrimaryVariant?->attributes['length'] ?? null)->toBe('40')
        ->and($betonPrimaryVariant?->attributes['width'] ?? null)->toBe('30')
        ->and($betonPrimaryVariant?->attributes['height'] ?? null)->toBe('10')
        ->and((float) ($betonPrimaryVariant?->variant_attribute_matrix['weight'] ?? 0))->toBe(20.0)
        ->and((float) ($betonPrimaryVariant?->variant_attribute_matrix['length'] ?? 0))->toBe(40.0)
        ->and((float) ($betonPrimaryVariant?->variant_attribute_matrix['width'] ?? 0))->toBe(30.0)
        ->and((float) ($betonPrimaryVariant?->variant_attribute_matrix['height'] ?? 0))->toBe(10.0)
        ->and($betonPrimaryVariant?->variant_combination_hash)->not->toBeNull();

    expect($duluxVariants)->toHaveCount(1)
        ->and($duluxVariants?->first()?->sku)->toBe('DAZ-001-R');

    expect($beton?->brand?->name)->toBe('Acme Build')
        ->and($dulux?->brand?->name)->toBe('Dulux');
});

it('keeps comma decimals for imported variant dimensions and comma text fields', function (): void {
    $user = User::factory()->admin()->create();

    $import = new Import;
    $import->user()->associate($user);
    $import->file_name = 'product-import.csv';
    $import->file_path = base_path('storage/imports/product-import.csv');
    $import->importer = ProductImporter::class;
    $import->total_rows = 1;
    $import->save();

    $columns = collect(ProductImporter::getColumns())->map->getName()->values();
    $row = $columns->mapWithKeys(fn (string $name) => [$name => ''])->all();
    $row['name'] = 'Comma Decimal Variant';
    $row['sku'] = 'COMMA-DEC-001';
    $row['price'] = '19.99';
    $row['weight'] = '4,2';
    $row['length'] = '12,5';
    $row['width'] = '7,25';
    $row['height'] = '1,5';
    $row['size'] = '4,2';
    $row['size_type'] = 'Type, A';
    $row['pack_size'] = '1,5';
    $row['color'] = 'Gray';

    $columnMap = $columns->mapWithKeys(fn (string $name) => [$name => $name])->all();

    (new ImportCsv($import, [$row], $columnMap, []))->handle();

    $product = Product::withoutGlobalScopes()->where('sku', 'COMMA-DEC-001')->first();
    $variant = $product?->variants()->withoutGlobalScopes()->first();

    expect($variant)->not->toBeNull()
        ->and((float) ($variant?->weight ?? 0))->toBe(4.2)
        ->and($variant?->size)->toBe('4,2')
        ->and($variant?->attributes['size_type'] ?? null)->toBe('Type, A')
        ->and($variant?->attributes['pack_size'] ?? null)->toBe('1,5')
        ->and($variant?->attributes['length'] ?? null)->toBe('12.5')
        ->and($variant?->attributes['width'] ?? null)->toBe('7.25')
        ->and($variant?->attributes['height'] ?? null)->toBe('1.5')
        ->and((float) ($variant?->variant_attribute_matrix['weight'] ?? 0))->toBe(4.2)
        ->and((float) ($variant?->variant_attribute_matrix['length'] ?? 0))->toBe(12.5)
        ->and((float) ($variant?->variant_attribute_matrix['width'] ?? 0))->toBe(7.25)
        ->and((float) ($variant?->variant_attribute_matrix['height'] ?? 0))->toBe(1.5)
        ->and($variant?->variant_attribute_matrix['size'] ?? null)->toBe('4,2')
        ->and($variant?->variant_attribute_matrix['pack_size'] ?? null)->toBe('1,5');
});

it('updates existing variant instead of duplicating when sku is imported again', function (): void {
    $user = User::factory()->admin()->create();

    $import = new Import;
    $import->user()->associate($user);
    $import->file_name = 'product-import.csv';
    $import->file_path = base_path('storage/imports/product-import.csv');
    $import->importer = ProductImporter::class;
    $import->total_rows = 2;
    $import->save();

    $columns = collect(ProductImporter::getColumns())->map->getName()->values();
    $baseRow = $columns->mapWithKeys(fn (string $name) => [$name => ''])->all();

    $rows = [
        array_merge($baseRow, [
            'name'           => 'Beton M-150',
            'sku'            => 'BET-001',
            'price'          => '12.50',
            'stock_quantity' => '100',
            'size'           => '20kg',
        ]),
        array_merge($baseRow, [
            'name'           => 'Beton M-150',
            'sku'            => 'BET-001',
            'price'          => '15.00',
            'stock_quantity' => '200',
            'size'           => '20kg',
        ]),
    ];

    $columnMap = $columns->mapWithKeys(fn (string $name) => [$name => $name])->all();

    (new ImportCsv($import, $rows, $columnMap, []))->handle();

    $product = Product::withoutGlobalScopes()->where('name', 'Beton M-150')->first();
    $variant = ProductVariant::withoutGlobalScopes()
        ->where('product_id', $product?->getKey())
        ->where('sku', 'BET-001')
        ->first();

    expect($product)->not->toBeNull()
        ->and(ProductVariant::withoutGlobalScopes()->where('sku', 'BET-001')->count())->toBe(1)
        ->and($variant)->not->toBeNull()
        ->and((float) ($variant?->price ?? 0))->toBe(15.0)
        ->and($variant?->stock_quantity)->toBe(200);
});

it('creates distinct variants when sku is repeated but option fields differ', function (): void {
    $user = User::factory()->admin()->create();

    $import = new Import;
    $import->user()->associate($user);
    $import->file_name = 'product-import.csv';
    $import->file_path = base_path('storage/imports/product-import.csv');
    $import->importer = ProductImporter::class;
    $import->total_rows = 4;
    $import->save();

    $columns = collect(ProductImporter::getColumns())->map->getName()->values();
    $baseRow = $columns->mapWithKeys(fn (string $name) => [$name => ''])->all();

    $rows = [
        array_merge($baseRow, [
            'name'           => 'Vyriški marškinėliai CHANCE 810',
            'sku'            => 'SKU00322',
            'price'          => '19.99',
            'size'           => 'S',
            'color'          => 'Juoda',
            'size_type'      => 'Adult',
            'pack_size'      => '1',
            'pack_size_type' => 'pcs',
        ]),
        array_merge($baseRow, [
            'name'           => 'Vyriški marškinėliai CHANCE 810',
            'sku'            => 'SKU00322',
            'price'          => '19.99',
            'size'           => 'M',
            'color'          => 'Juoda',
            'size_type'      => 'Adult',
            'pack_size'      => '1',
            'pack_size_type' => 'pcs',
        ]),
        array_merge($baseRow, [
            'name'           => 'Vyriški marškinėliai CHANCE 810',
            'sku'            => 'SKU00322',
            'price'          => '19.99',
            'size'           => 'S',
            'color'          => 'Balta',
            'size_type'      => 'Adult',
            'pack_size'      => '1',
            'pack_size_type' => 'pcs',
        ]),
        array_merge($baseRow, [
            'name'           => 'Vyriški marškinėliai CHANCE 810',
            'sku'            => 'SKU00322',
            'price'          => '19.99',
            'size'           => 'M',
            'color'          => 'Balta',
            'size_type'      => 'Adult',
            'pack_size'      => '1',
            'pack_size_type' => 'pcs',
        ]),
    ];

    $columnMap = $columns->mapWithKeys(fn (string $name) => [$name => $name])->all();

    (new ImportCsv($import, $rows, $columnMap, []))->handle();

    $product = Product::withoutGlobalScopes()
        ->where('name', 'Vyriški marškinėliai CHANCE 810')
        ->first();

    expect($import->fresh()->successful_rows)->toBe(4)
        ->and($import->fresh()->failedRows)->toHaveCount(0)
        ->and($product)->not->toBeNull()
        ->and(Product::withoutGlobalScopes()->where('name', 'Vyriški marškinėliai CHANCE 810')->count())->toBe(1);

    $variants = $product?->variants()->withoutGlobalScopes()->get();

    expect($variants)->toHaveCount(4)
        ->and($variants?->pluck('variant_combination_hash')->filter()->unique()->count())->toBe(4)
        ->and($variants?->pluck('attributes')->filter()->count())->toBe(4);
});

it('does not fail repeated same-category rows and does not duplicate category pivot', function (): void {
    $user = User::factory()->admin()->create();

    $import = new Import;
    $import->user()->associate($user);
    $import->file_name = 'product-import.csv';
    $import->file_path = base_path('storage/imports/product-import.csv');
    $import->importer = ProductImporter::class;
    $import->total_rows = 3;
    $import->save();

    $columns = collect(ProductImporter::getColumns())->map->getName()->values();
    $baseRow = $columns->mapWithKeys(fn (string $name) => [$name => ''])->all();

    $rows = [
        array_merge($baseRow, [
            'name'       => 'Category Pivot Product',
            'sku'        => 'CAT-ROW-001',
            'price'      => '5.00',
            'categories' => 'Darbo apranga, saugos priemonės / Marškinėliai',
            'size'       => 'S',
            'color'      => 'Juoda',
        ]),
        array_merge($baseRow, [
            'name'       => 'Category Pivot Product',
            'sku'        => 'CAT-ROW-001',
            'price'      => '5.00',
            'categories' => 'Darbo apranga, saugos priemonės / Marškinėliai',
            'size'       => 'M',
            'color'      => 'Juoda',
        ]),
        array_merge($baseRow, [
            'name'       => 'Category Pivot Product',
            'sku'        => 'CAT-ROW-001',
            'price'      => '5.00',
            'categories' => 'Darbo apranga, saugos priemonės / Marškinėliai',
            'size'       => 'L',
            'color'      => 'Juoda',
        ]),
    ];

    $columnMap = $columns->mapWithKeys(fn (string $name) => [$name => $name])->all();

    (new ImportCsv($import, $rows, $columnMap, []))->handle();

    $product = Product::withoutGlobalScopes()->where('name', 'Category Pivot Product')->first();

    expect($import->fresh()->successful_rows)->toBe(3)
        ->and($import->fresh()->failedRows)->toHaveCount(0)
        ->and($product)->not->toBeNull();

    $categoryCount = $product?->categories()->withoutGlobalScopes()->count() ?? 0;

    expect($categoryCount)->toBe(1);
});

it('writes frontend variant option attribute values from imported rows', function (): void {
    $user = User::factory()->admin()->create();

    $import = new Import;
    $import->user()->associate($user);
    $import->file_name = 'product-import.csv';
    $import->file_path = base_path('storage/imports/product-import.csv');
    $import->importer = ProductImporter::class;
    $import->total_rows = 1;
    $import->save();

    $columns = collect(ProductImporter::getColumns())->map->getName()->values();
    $row = $columns->mapWithKeys(fn (string $name) => [$name => ''])->all();
    $row['name'] = 'Variant Option Value Product';
    $row['sku'] = 'VAV-001';
    $row['price'] = '9.99';
    $row['size'] = 'XL';
    $row['size_type'] = 'Adult';
    $row['pack_size'] = '1';
    $row['pack_size_type'] = 'pcs';
    $row['color'] = 'Mėlyna';
    $row['weight'] = '2.5';
    $row['length'] = '45';
    $row['width'] = '30';
    $row['height'] = '15';

    $columnMap = $columns->mapWithKeys(fn (string $name) => [$name => $name])->all();

    (new ImportCsv($import, [$row], $columnMap, []))->handle();

    $product = Product::withoutGlobalScopes()->where('name', 'Variant Option Value Product')->first();
    $variant = $product?->variants()->withoutGlobalScopes()->first();

    expect($import->fresh()->successful_rows)->toBe(1)
        ->and($variant)->not->toBeNull();

    $attributeValues = $variant?->variantAttributeValues()
        ->withoutGlobalScopes()
        ->pluck('attribute_value')
        ->all() ?? [];

    if ($attributeValues !== []) {
        expect($attributeValues)->toContain('Mėlyna')
            ->and($attributeValues)->toContain('XL')
            ->and($attributeValues)->toContain('Adult')
            ->and($attributeValues)->toContain('1')
            ->and($attributeValues)->toContain('pcs')
            ->and($attributeValues)->toContain('2.5')
            ->and($attributeValues)->toContain('45')
            ->and($attributeValues)->toContain('30')
            ->and($attributeValues)->toContain('15');

        return;
    }

    expect($variant?->attributes['color'] ?? null)->toBe('Mėlyna')
        ->and($variant?->size)->toBe('XL')
        ->and($variant?->attributes['size_type'] ?? null)->toBe('Adult')
        ->and($variant?->attributes['pack_size'] ?? null)->toBe('1')
        ->and($variant?->attributes['pack_size_type'] ?? null)->toBe('pcs')
        ->and($variant?->attributes['weight'] ?? null)->toBe('2.5')
        ->and($variant?->attributes['length'] ?? null)->toBe('45')
        ->and($variant?->attributes['width'] ?? null)->toBe('30')
        ->and($variant?->attributes['height'] ?? null)->toBe('15');
});
