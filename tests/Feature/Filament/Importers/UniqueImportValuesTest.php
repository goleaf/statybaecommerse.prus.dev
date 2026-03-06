<?php

declare(strict_types=1);

use App\Filament\Imports\UserImporter;
use App\Models\User;
use Filament\Actions\Imports\Jobs\ImportCsv;
use Filament\Actions\Imports\Models\Import;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('appends a numeric suffix to duplicate emails during import', function () {
    $user = User::factory()->admin()->create();
    User::factory()->create([
        'email' => 'info@egisstatyba.lt',
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
    $row['email'] = 'info@egisstatyba.lt';
    $row['password'] = 'Password1!';
    $row['is_active'] = '1';

    $columnMap = $columns->mapWithKeys(fn (string $name) => [$name => $name])->all();

    (new ImportCsv($import, [$row], $columnMap, []))->handle();

    expect(User::query()->where('email', 'like', 'info@egisstatyba.lt')->exists())->toBeTrue();
});
