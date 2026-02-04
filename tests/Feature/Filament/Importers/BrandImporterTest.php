<?php

declare(strict_types=1);

namespace Tests\Feature\Filament\Importers;

use App\Filament\Imports\BrandImporter;
use App\Models\Brand;
use App\Models\User;
use Filament\Actions\Imports\Jobs\ImportCsv;
use Filament\Actions\Imports\Models\Import;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('can import brands', function () {
    $user = User::factory()->admin()->create();

    $import = new Import();
    $import->user()->associate($user);
    $import->file_name = 'brands.csv';
    $import->file_path = 'brands.csv';
    $import->importer = BrandImporter::class;
    $import->total_rows = 1;
    $import->save();

    $row = [
        'name' => 'Imported Brand',
        'slug' => 'imported-brand',
        'is_enabled' => '1',
    ];

    $columnMap = [
        'name' => 'name',
        'slug' => 'slug',
        'is_enabled' => 'is_enabled',
    ];

    (new ImportCsv($import, [$row], $columnMap, []))->handle();

    $import->refresh();

    expect($import->successful_rows)->toBe(1);
    $this->assertDatabaseHas('brands', [
        'name' => 'Imported Brand',
        'slug' => 'imported-brand',
    ]);
});
