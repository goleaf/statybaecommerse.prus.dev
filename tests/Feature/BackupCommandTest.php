<?php

declare(strict_types=1);

use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

use function Pest\Laravel\artisan;

it('prepares artifacts and verifies them on an ephemeral connection', function (): void {
    Storage::fake('backups');

    Config::set('backup.disk', 'backups');
    Config::set('backup.directory', 'artifacts');
    Config::set('backup.verify.connection', 'backup_ephemeral');
    Config::set('database.connections.backup_ephemeral', [
        'driver' => 'sqlite',
        'database' => ':memory:',
        'prefix' => '',
        'foreign_key_constraints' => true,
    ]);

    $users = User::factory()->count(3)->create([
        'name' => 'Test Admin',
    ]);

    $products = Product::factory()->count(4)->create([
        'manage_stock' => true,
        'stock_quantity' => 25,
        'is_visible' => true,
    ]);

    /** @var \Illuminate\Testing\PendingCommand $prepare */
    $prepare = artisan('backup:prepare');
    $prepare->assertSuccessful();

    $disk = Storage::disk('backups');
    $artifactRoot = collect($disk->directories('artifacts'))->sort()->last();
    expect($artifactRoot)->not()->toBeNull();
    assert(is_string($artifactRoot));

    expect($disk->exists($artifactRoot.'/users.json'))->toBeTrue();
    expect($disk->exists($artifactRoot.'/products.json'))->toBeTrue();
    expect($disk->exists($artifactRoot.'/manifest.json'))->toBeTrue();

    /** @var array{counts?: array{users?: int, products?: int}} $manifest */
    $manifest = json_decode((string) $disk->get($artifactRoot.'/manifest.json'), true, 512, JSON_THROW_ON_ERROR);
    expect(Arr::get($manifest, 'counts.users'))->toBe($users->count());
    expect(Arr::get($manifest, 'counts.products'))->toBe($products->count());

    /** @var \Illuminate\Testing\PendingCommand $verify */
    $verify = artisan('backup:verify', [
        'path' => $artifactRoot,
        '--connection' => 'backup_ephemeral',
    ]);
    $verify->assertSuccessful();

    $ephemeral = DB::connection('backup_ephemeral');
    expect($ephemeral->table('users')->count())->toBe($users->count());
    expect($ephemeral->table('products')->count())->toBe($products->count());
});
