<?php

declare(strict_types=1);

namespace Tests\Feature\Filament\Importers;

use App\Filament\Imports\PartnerImporter;
use App\Models\Partner;
use App\Models\User;
use Filament\Actions\Imports\Jobs\ImportCsv;
use Filament\Actions\Imports\Models\Import;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('can import partners', function () {
    $user = User::factory()->admin()->create();

    $import = new Import();
    $import->user()->associate($user);
    $import->file_name = 'partners.csv';
    $import->file_path = 'partners.csv';
    $import->importer = PartnerImporter::class;
    $import->total_rows = 1;
    $import->save();

    $row = [
        'name' => 'Imported Partner',
        'code' => 'IP-001',
        'contact_email' => 'partner@example.com',
        'is_enabled' => '1',
    ];

    $columnMap = [
        'name' => 'name',
        'code' => 'code',
        'contact_email' => 'contact_email',
        'is_enabled' => 'is_enabled',
    ];

    (new ImportCsv($import, [$row], $columnMap, []))->handle();

    $import->refresh();

    expect($import->successful_rows)->toBe(1);
    $this->assertDatabaseHas('partners', [
        'name' => 'Imported Partner',
        'code' => 'IP-001',
    ]);
});
