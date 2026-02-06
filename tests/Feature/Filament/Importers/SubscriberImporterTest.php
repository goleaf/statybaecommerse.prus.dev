<?php

declare(strict_types=1);

namespace Tests\Feature\Filament\Importers;

use App\Filament\Imports\SubscriberImporter;
use App\Models\User;
use Filament\Actions\Imports\Jobs\ImportCsv;
use Filament\Actions\Imports\Models\Import;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('can import subscribers', function () {
    $user = User::factory()->admin()->create();

    $import = new Import;
    $import->user()->associate($user);
    $import->file_name = 'subscribers.csv';
    $import->file_path = 'subscribers.csv';
    $import->importer = SubscriberImporter::class;
    $import->total_rows = 1;
    $import->save();

    $row = [
        'email'      => 'subscriber@example.com',
        'first_name' => 'Sub',
        'last_name'  => 'Scrib',
        'status'     => 'subscribed',
    ];

    $columnMap = [
        'email'      => 'email',
        'first_name' => 'first_name',
        'last_name'  => 'last_name',
        'status'     => 'status',
    ];

    (new ImportCsv($import, [$row], $columnMap, []))->handle();

    $import->refresh();

    expect($import->successful_rows)->toBe(1);
    $this->assertDatabaseHas('subscribers', [
        'email' => 'subscriber@example.com',
    ]);
});
