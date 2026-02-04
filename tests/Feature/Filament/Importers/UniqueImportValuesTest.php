<?php

declare(strict_types=1);

use App\Filament\Imports\OrganizationImporter;
use App\Filament\Imports\UserImporter;
use App\Models\Organization;
use App\Models\User;
use Filament\Actions\Imports\Jobs\ImportCsv;
use Filament\Actions\Imports\Models\Import;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('appends a numeric suffix to duplicate slugs during import', function () {
    $user = User::factory()->admin()->create();
    Organization::factory()->create([
        'slug' => 'acme',
    ]);

    $import = new Import;
    $import->user()->associate($user);
    $import->file_name = 'organizations.csv';
    $import->file_path = base_path('storage/imports/organizations.csv');
    $import->importer = OrganizationImporter::class;
    $import->total_rows = 1;
    $import->save();

    $columns = collect(OrganizationImporter::getColumns())->map->getName()->values();
    $row = $columns->mapWithKeys(fn (string $name) => [$name => ''])->all();
    $row['name'] = 'Acme';
    $row['slug'] = 'acme';
    $row['type'] = 'company';
    $row['is_active'] = '1';

    $columnMap = $columns->mapWithKeys(fn (string $name) => [$name => $name])->all();

    (new ImportCsv($import, [$row], $columnMap, []))->handle();

    expect(Organization::query()->where('slug', 'acme-1')->exists())->toBeTrue();
});

it('appends a numeric suffix to duplicate emails during import', function () {
    $user = User::factory()->admin()->create();
    User::factory()->create([
        'email' => 'john@example.com',
    ]);

    $import = new Import;
    $import->user()->associate($user);
    $import->file_name = 'users.csv';
    $import->file_path = base_path('storage/imports/users.csv');
    $import->importer = UserImporter::class;
    $import->total_rows = 1;
    $import->save();

    $columns = collect(UserImporter::getColumns())->map->getName()->values();
    $row = $columns->mapWithKeys(fn (string $name) => [$name => ''])->all();
    $row['name'] = 'John Doe';
    $row['email'] = 'john@example.com';
    $row['password'] = 'Password1!';
    $row['is_active'] = '1';

    $columnMap = $columns->mapWithKeys(fn (string $name) => [$name => $name])->all();

    (new ImportCsv($import, [$row], $columnMap, []))->handle();

    expect(User::query()->where('email', 'like', 'john-%@example.com')->exists())->toBeTrue();
});
