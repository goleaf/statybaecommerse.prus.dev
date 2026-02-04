<?php

declare(strict_types=1);

namespace Tests\Feature\Filament\Importers;

use App\Filament\Imports\OrganizationImporter;
use App\Models\Organization;
use App\Models\User;
use Filament\Actions\Imports\Jobs\ImportCsv;
use Filament\Actions\Imports\Models\Import;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('can import organizations', function () {
    $user = User::factory()->admin()->create();

    $import = new Import();
    $import->user()->associate($user);
    $import->file_name = 'organizations.csv';
    $import->file_path = 'organizations.csv';
    $import->importer = OrganizationImporter::class;
    $import->total_rows = 1;
    $import->save();

    $row = [
        'name' => 'Imported Organization',
        'slug' => 'imported-organization',
        'is_active' => '1',
    ];

    $columnMap = [
        'name' => 'name',
        'slug' => 'slug',
        'is_active' => 'is_active',
    ];

    (new ImportCsv($import, [$row], $columnMap, []))->handle();

    $import->refresh();

    expect($import->successful_rows)->toBe(1);
    $this->assertDatabaseHas('organizations', [
        'name' => 'Imported Organization',
        'slug' => 'imported-organization',
    ]);
});
