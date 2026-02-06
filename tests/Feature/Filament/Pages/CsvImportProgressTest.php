<?php

declare(strict_types=1);

use App\Filament\Pages\Imports\ImportCategories;
use App\Models\AdminUser;
use App\Models\ImportRowResult;
use App\Models\User;
use Filament\Actions\Imports\Models\Import;
use Illuminate\Support\Carbon;

use function Pest\Livewire\livewire;

it('tracks running import progress', function () {
    $admin = AdminUser::factory()->create();
    $user = User::factory()->create();

    $import = Import::query()->create([
        'completed_at'    => null,
        'file_name'       => 'categories.csv',
        'file_path'       => 'imports/csv/categories.csv',
        'importer'        => ImportCategories::getImporter(),
        'processed_rows'  => 40,
        'total_rows'      => 100,
        'successful_rows' => 35,
        'user_id'         => $user->id,
    ]);

    $this->actingAs($admin, 'admin');

    livewire(ImportCategories::class)
        ->set('activeImportId', $import->getKey())
        ->set('isImporting', true)
        ->call('refreshImportProgress')
        ->assertSet('importProgress.percent', 40)
        ->assertSet('importProgress.failed', 5)
        ->assertSet('importProgress.status', 'running');
});

it('marks import as completed and updates summary', function () {
    $admin = AdminUser::factory()->create();
    $user = User::factory()->create();

    $import = Import::query()->create([
        'completed_at'    => Carbon::now(),
        'file_name'       => 'categories.csv',
        'file_path'       => 'imports/csv/categories.csv',
        'importer'        => ImportCategories::getImporter(),
        'processed_rows'  => 100,
        'total_rows'      => 100,
        'successful_rows' => 100,
        'user_id'         => $user->id,
    ]);

    $this->actingAs($admin, 'admin');

    livewire(ImportCategories::class)
        ->set('activeImportId', $import->getKey())
        ->set('isImporting', true)
        ->call('refreshImportProgress')
        ->assertSet('importProgress.status', 'completed')
        ->assertSet('importProgress.failed', 0)
        ->assertSet('isImporting', false)
        ->assertSet('lastImport.total', 100);
});

it('does not show failures before rows are processed', function () {
    $admin = AdminUser::factory()->create();
    $user = User::factory()->create();

    $import = Import::query()->create([
        'completed_at'    => null,
        'file_name'       => 'categories.csv',
        'file_path'       => 'imports/csv/categories.csv',
        'importer'        => ImportCategories::getImporter(),
        'processed_rows'  => 0,
        'total_rows'      => 120,
        'successful_rows' => 0,
        'user_id'         => $user->id,
    ]);

    $this->actingAs($admin, 'admin');

    livewire(ImportCategories::class)
        ->set('activeImportId', $import->getKey())
        ->set('isImporting', true)
        ->call('refreshImportProgress')
        ->assertSet('importProgress.failed', 0)
        ->assertSet('importProgress.status', 'running');
});

it('renders the latest import rows table', function () {
    $admin = AdminUser::factory()->create();
    $user = User::factory()->create();

    $import = Import::query()->create([
        'completed_at'    => Carbon::now(),
        'file_name'       => 'categories.csv',
        'file_path'       => 'imports/csv/categories.csv',
        'importer'        => ImportCategories::getImporter(),
        'processed_rows'  => 20,
        'total_rows'      => 20,
        'successful_rows' => 20,
        'user_id'         => $user->id,
    ]);

    ImportRowResult::query()->create([
        'import_id'      => $import->getKey(),
        'row_number'     => 1,
        'status'         => 'success',
        'action'         => 'created',
        'message'        => 'Created.',
        'changed_fields' => ['name'],
        'data'           => ['name' => 'Example item'],
    ]);

    $this->actingAs($admin, 'admin');

    livewire(ImportCategories::class)
        ->set('activeImportId', $import->getKey())
        ->call('refreshImportProgress')
        ->assertSee(__('admin.import_rows_latest'))
        ->assertSee('Example item');
});
