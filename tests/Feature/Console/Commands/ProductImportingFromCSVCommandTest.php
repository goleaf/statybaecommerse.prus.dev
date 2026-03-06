<?php

declare(strict_types=1);

use App\Models\Product;
use App\Models\User;
use Filament\Actions\Imports\Models\Import;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;

use function Pest\Laravel\artisan;

uses(RefreshDatabase::class);

function createProductImportCommandCsv(array $rows): string
{
    $path = storage_path('framework/testing/product-import-command-' . Str::uuid() . '.csv');
    $directory = dirname($path);

    if (! is_dir($directory)) {
        mkdir($directory, 0777, true);
    }

    $handle = fopen($path, 'w');
    if (! is_resource($handle)) {
        throw new RuntimeException('Unable to create CSV fixture.');
    }

    fputcsv($handle, ['name', 'sku', 'price']);

    foreach ($rows as $row) {
        fputcsv($handle, [
            (string) ($row['name'] ?? ''),
            (string) ($row['sku'] ?? ''),
            (string) ($row['price'] ?? ''),
        ]);
    }

    fclose($handle);

    return $path;
}

it('updates an existing product matched by sku without creating duplicates', function (): void {
    User::factory()->admin()->create();

    $product = Product::factory()->create([
        'name' => 'Original Name',
        'sku'  => 'CMD-SKU-001',
    ]);

    $path = createProductImportCommandCsv([
        ['name' => 'Updated Name', 'sku' => 'CMD-SKU-001', 'price' => '20.50'],
    ]);

    artisan('import:products', ['path' => $path])
        ->assertExitCode(0);

    $product->refresh();
    $latestImport = Import::query()->latest('id')->first();

    expect($product->name)->toBe('Updated Name')
        ->and(Product::withoutGlobalScopes()->where('sku', 'CMD-SKU-001')->count())->toBe(1)
        ->and($latestImport)->not->toBeNull()
        ->and((int) $latestImport?->processed_rows)->toBe(1)
        ->and((int) $latestImport?->successful_rows)->toBe(1);
});

it('marks unmatched sku rows as failed and does not create new products', function (): void {
    User::factory()->admin()->create();

    $path = createProductImportCommandCsv([
        ['name' => 'Missing Product', 'sku' => 'MISSING-SKU-001', 'price' => '10.00'],
    ]);

    artisan('import:products', ['path' => $path])
        ->assertExitCode(0);

    $latestImport = Import::query()->latest('id')->first();

    expect(Product::withoutGlobalScopes()->where('sku', 'MISSING-SKU-001')->exists())->toBeFalse()
        ->and($latestImport)->not->toBeNull()
        ->and((int) $latestImport?->processed_rows)->toBe(1)
        ->and((int) $latestImport?->successful_rows)->toBe(0)
        ->and((int) $latestImport?->failedRows()->count())->toBe(1);
});

it('fails fast when there is no admin user', function (): void {
    $path = createProductImportCommandCsv([
        ['name' => 'Any Product', 'sku' => 'SKU-ANY-001', 'price' => '5.99'],
    ]);

    artisan('import:products', ['path' => $path])
        ->assertExitCode(1);

    expect(Import::query()->count())->toBe(0);
});

it('processes imports in configured chunk sizes', function (): void {
    User::factory()->admin()->create();

    $first = Product::factory()->create([
        'name' => 'First Original',
        'sku'  => 'CMD-CHUNK-001',
    ]);

    $second = Product::factory()->create([
        'name' => 'Second Original',
        'sku'  => 'CMD-CHUNK-002',
    ]);

    $path = createProductImportCommandCsv([
        ['name' => 'First Updated', 'sku' => 'CMD-CHUNK-001', 'price' => '3.50'],
        ['name' => 'Second Updated', 'sku' => 'CMD-CHUNK-002', 'price' => '4.50'],
    ]);

    artisan('import:products', ['path' => $path, '--chunk' => 1])
        ->assertExitCode(0);

    $first->refresh();
    $second->refresh();
    $latestImport = Import::query()->latest('id')->first();

    expect($first->name)->toBe('First Updated')
        ->and($second->name)->toBe('Second Updated')
        ->and($latestImport)->not->toBeNull()
        ->and((int) $latestImport?->processed_rows)->toBe(2)
        ->and((int) $latestImport?->successful_rows)->toBe(2)
        ->and((int) $latestImport?->total_rows)->toBe(2)
        ->and($latestImport?->completed_at)->not->toBeNull();
});
