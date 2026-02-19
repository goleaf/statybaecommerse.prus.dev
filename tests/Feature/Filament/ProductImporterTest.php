<?php

declare(strict_types=1);

use App\Filament\Imports\ProductImporter;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\User;
use Filament\Actions\Imports\Jobs\ImportCsv;
use Filament\Actions\Imports\Models\FailedImportRow;
use Filament\Actions\Imports\Models\Import;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

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
        'https://example.com/images/product.png' => Http::response('fake-image-bytes', 200, [
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
        ->and($storedPath)->toEndWith('.png');

    Storage::disk('public')->assertExists($storedPath);
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
