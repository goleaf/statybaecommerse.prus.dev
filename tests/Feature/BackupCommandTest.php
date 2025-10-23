<?php

declare(strict_types=1);

use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

it('prepares and verifies backup artifacts on an ephemeral connection', function (): void {
    /** @var \Tests\TestCase $this */
    Storage::fake('backups');

    $users = User::factory()->count(3)->create();
    $products = Product::factory()->count(4)->create();

    /** @var \Illuminate\Testing\PendingCommand $prepareCommand */
    $prepareCommand = $this->artisan('backup:prepare', ['--disk' => 'backups']);
    $prepareCommand->assertSuccessful();

    $disk = Storage::disk('backups');
    $disk->assertExists('artifacts/users.json');
    $disk->assertExists('artifacts/products.json');

    $backupDbPath = storage_path('framework/testing/backup.sqlite');
    if (file_exists($backupDbPath)) {
        unlink($backupDbPath);
    }

    if (! is_dir(dirname($backupDbPath))) {
        mkdir(dirname($backupDbPath), 0777, true);
    }

    Config::set('database.connections.backup', [
        'driver' => 'sqlite',
        'database' => $backupDbPath,
        'prefix' => '',
        'foreign_key_constraints' => false,
    ]);

    /** @var \Illuminate\Testing\PendingCommand $verifyCommand */
    $verifyCommand = $this->artisan('backup:verify', [
        '--disk' => 'backups',
        '--connection' => 'backup',
    ]);
    $verifyCommand->assertSuccessful();

    $backupConnection = DB::connection('backup');

    expect($backupConnection->table('users')->count())->toBe($users->count());
    expect($backupConnection->table('products')->count())->toBe($products->count());

    $backupConnection->disconnect();

    if (file_exists($backupDbPath)) {
        unlink($backupDbPath);
    }
});
