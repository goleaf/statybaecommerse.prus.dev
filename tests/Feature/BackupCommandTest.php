<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

final class BackupCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_backup_prepare_and_verify_commands_preserve_catalog_state(): void
    {
        Storage::fake('backups');

        Config::set('database.connections.ephemeral_verify', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'foreign_key_constraints' => true,
        ]);

        $users = User::factory()
            ->count(3)
            ->create();

        $products = Product::factory()
            ->count(4)
            ->create();

        $artifactPath = 'nightly/catalog-backup.json';

        $this->artisan('backup:prepare', [
            '--disk' => 'backups',
            '--path' => $artifactPath,
        ])
            ->expectsOutputToContain('Backup prepared on disk [backups]')
            ->assertSuccessful();

        $this->assertTrue(Storage::disk('backups')->exists($artifactPath));

        $payload = json_decode(Storage::disk('backups')->get($artifactPath), true, 512, JSON_THROW_ON_ERROR);

        $this->assertSame($users->count(), $payload['metadata']['user_count']);
        $this->assertSame($products->count(), $payload['metadata']['product_count']);

        $this->artisan('backup:verify', [
            '--disk' => 'backups',
            '--path' => $artifactPath,
            '--connection' => 'ephemeral_verify',
        ])
            ->expectsOutputToContain('Backup verified on connection [ephemeral_verify]')
            ->assertSuccessful();

        $this->assertSame($users->count(), User::count());
        $this->assertSame($products->count(), Product::count());
    }
}
