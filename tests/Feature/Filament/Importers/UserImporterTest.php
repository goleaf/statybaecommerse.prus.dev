<?php

declare(strict_types=1);

namespace Tests\Feature\Filament\Importers;

use App\Filament\Imports\UserImporter;
use App\Models\User;
use Filament\Actions\Imports\Jobs\ImportCsv;
use Filament\Actions\Imports\Models\Import;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

it('can import users', function () {
    $admin = User::factory()->admin()->create();

    $import = new Import;
    $import->user()->associate($admin);
    $import->file_name = 'users.csv';
    $import->file_path = 'users.csv';
    $import->importer = UserImporter::class;
    $import->total_rows = 1;
    $import->save();

    $row = [
        'name'      => 'John Doe',
        'email'     => 'info@egisstatyba.lt',
        'password'  => 'Password123!',
        'is_admin'  => '0',
        'is_active' => '1',
    ];

    $columnMap = [
        'name'      => 'name',
        'email'     => 'email',
        'password'  => 'password',
        'is_admin'  => 'is_admin',
        'is_active' => 'is_active',
    ];

    (new ImportCsv($import, [$row], $columnMap, []))->handle();

    $import->refresh();

    expect($import->successful_rows)->toBe(1);
    $this->assertDatabaseHas('users', [
        'name'  => 'John Doe',
        'email' => 'info@egisstatyba.lt',
    ]);

    $user = User::where('email', 'info@egisstatyba.lt')->first();
    expect(Hash::check('Password123!', $user->password))->toBeTrue();
});
