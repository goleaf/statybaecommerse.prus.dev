<?php

declare(strict_types=1);

use App\Filament\Imports\ProductImporter;
use App\Models\AdminUser;
use App\Models\User;
use Filament\Actions\Imports\Models\FailedImportRow;
use Filament\Actions\Imports\Models\Import;
use Illuminate\Support\Facades\URL;

it('allows admin users to download failed import rows', function () {
    $admin = AdminUser::factory()->create();
    $user = User::factory()->create();

    $import = Import::query()->create([
        'file_name'       => 'products.csv',
        'file_path'       => 'imports/products.csv',
        'file_disk'       => 'local',
        'importer'        => ProductImporter::class,
        'processed_rows'  => 1,
        'total_rows'      => 1,
        'successful_rows' => 0,
        'user_id'         => $user->getKey(),
    ]);

    FailedImportRow::query()->create([
        'import_id'        => $import->getKey(),
        'data'             => ['sku' => 'ABC-123'],
        'validation_error' => 'Invalid SKU',
    ]);

    $url = URL::signedRoute('filament.imports.failed-rows.download', [
        'authGuard' => 'admin',
        'import'    => $import,
    ], absolute: false);

    $response = $this->actingAs($admin, 'admin')->get($url);

    $response->assertSuccessful();
    $response->assertHeaderContains('Content-Type', 'text/csv');
    $response->assertHeaderContains('Content-Disposition', '.csv');
});
