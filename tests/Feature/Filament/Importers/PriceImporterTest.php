<?php

declare(strict_types=1);

namespace Tests\Feature\Filament\Importers;

use App\Filament\Imports\PriceImporter;
use App\Models\Currency;
use App\Models\Product;
use App\Models\User;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Jobs\ImportCsv;
use Filament\Actions\Imports\Models\Import;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('does not expose a currency import column', function (): void {
    $columnNames = collect(PriceImporter::getColumns())
        ->map(fn (ImportColumn $column): string => $column->getName())
        ->all();

    expect($columnNames)->not->toContain('currency');
});

it('can import prices', function () {
    $user = User::factory()->admin()->create();
    $product = Product::factory()->create();
    $currency = Currency::factory()->create(['code' => 'EUR']);

    $import = new Import;
    $import->user()->associate($user);
    $import->file_name = 'prices.csv';
    $import->file_path = 'prices.csv';
    $import->importer = PriceImporter::class;
    $import->total_rows = 1;
    $import->save();

    $row = [
        'priceable_id'   => (string) $product->id,
        'priceable_type' => Product::class,
        'amount'         => '123.45',
        'is_enabled'     => '1',
    ];

    $columnMap = [
        'priceable_id'   => 'priceable_id',
        'priceable_type' => 'priceable_type',
        'amount'         => 'amount',
        'is_enabled'     => 'is_enabled',
    ];

    (new ImportCsv($import, [$row], $columnMap, []))->handle();

    $import->refresh();

    expect($import->successful_rows)->toBe(1);
    $this->assertDatabaseHas('prices', [
        'priceable_id'   => $product->id,
        'priceable_type' => Product::class,
        'currency_id'    => $currency->id,
        'amount'         => 123.45,
    ]);
});
