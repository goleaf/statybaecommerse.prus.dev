<?php

declare(strict_types=1);

namespace Tests\Feature\Filament\Importers;

use App\Filament\Imports\OrderImporter;
use App\Models\User;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Jobs\ImportCsv;
use Filament\Actions\Imports\Models\Import;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('does not expose a currency import column', function (): void {
    $columnNames = collect(OrderImporter::getColumns())
        ->map(fn (ImportColumn $column): string => $column->getName())
        ->all();

    expect($columnNames)->not->toContain('currency');
});

it('can import orders', function () {
    $user = User::factory()->create();
    $admin = User::factory()->admin()->create();

    $import = new Import;
    $import->user()->associate($admin);
    $import->file_name = 'orders.csv';
    $import->file_path = 'orders.csv';
    $import->importer = OrderImporter::class;
    $import->total_rows = 1;
    $import->save();

    $row = [
        'number'         => 'ORD-12345',
        'user'           => (string) $user->id,
        'status'         => 'pending',
        'total'          => '100.50',
        'payment_status' => 'paid',
    ];

    $columnMap = [
        'number'         => 'number',
        'user'           => 'user',
        'status'         => 'status',
        'total'          => 'total',
        'payment_status' => 'payment_status',
    ];

    (new ImportCsv($import, [$row], $columnMap, []))->handle();

    $import->refresh();

    expect($import->successful_rows)->toBe(1);
    $this->assertDatabaseHas('orders', [
        'number'   => 'ORD-12345',
        'user_id'  => $user->id,
        'total'    => 100.50,
        'currency' => 'EUR',
    ]);
});
