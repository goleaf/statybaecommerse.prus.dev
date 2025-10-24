<?php

declare(strict_types=1);

namespace Tests\Feature\Console;

use App\Models\Product;
use App\Models\User;
use App\Support\Repositories\ProductRepository;
use App\Support\Repositories\UserRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Testing\PendingCommand;
use Tests\TestCase;

final class BackupLifecycleTest extends TestCase
{
    public function test_sqlite_backup_prepare_and_verify_records_metadata_and_counts(): void
    {
        $databasePath = storage_path('framework/testing/backup-source.sqlite');
        $backupRoot = storage_path('framework/testing/backups');
        $verifyWorkingPath = storage_path('framework/testing/backup-verify-working');
        $verifyDatabasePath = storage_path('framework/testing/backup-verify/database.sqlite');

        File::ensureDirectoryExists(dirname($databasePath));
        File::delete($databasePath);
        File::deleteDirectory($backupRoot);
        File::deleteDirectory($verifyWorkingPath);
        File::deleteDirectory(dirname($verifyDatabasePath));

        config()->set('database.default', 'sqlite');
        config()->set('database.connections.sqlite.database', $databasePath);
        config()->set('backup.connection', 'sqlite');
        config()->set('backup.storage_path', $backupRoot);
        config()->set('backup.media_paths', []);
        config()->set('backup.repositories', [
            'users' => UserRepository::class,
            'products' => ProductRepository::class,
        ]);
        config()->set('backup.verify.working_path', $verifyWorkingPath);
        config()->set('backup.verify.connection_name', 'backup-verify-test');
        config()->set('backup.verify.connection', [
            'driver' => 'sqlite',
            'database' => $verifyDatabasePath,
            'prefix' => '',
            'foreign_key_constraints' => true,
        ]);

        File::put($databasePath, '');
        DB::purge('sqlite');
        DB::reconnect('sqlite');

        try {
            $this->artisan('migrate:fresh', ['--database' => 'sqlite'])->assertExitCode(0);

            User::factory()->count(2)->create();
            Product::factory()->count(3)->create();

            $this->artisan('backup:prepare', [
                '--connection' => 'sqlite',
                '--storage-path' => $backupRoot,
            ])->assertExitCode(0);

            $directories = collect(File::directories($backupRoot))->sort()->values();
            $this->assertNotEmpty($directories);
            $latestBackupPath = (string) $directories->last();

            $metadataPath = $latestBackupPath . '/metadata.json';
            $this->assertFileExists($metadataPath);

            $metadata = json_decode(File::get($metadataPath), true, 512, JSON_THROW_ON_ERROR);

            $this->assertSame('sqlite', $metadata['artifacts']['database']['driver'] ?? null);
            $this->assertSame([
                'users' => UserRepository::class,
                'products' => ProductRepository::class,
            ], $metadata['repositories'] ?? []);
            $this->assertSame(2, $metadata['counts']['users'] ?? null);
            $this->assertSame(3, $metadata['counts']['products'] ?? null);

            $this->artisan('backup:verify', [
                '--storage-path' => $backupRoot,
                '--working-path' => $verifyWorkingPath,
                '--connection' => 'backup-verify-test',
            ])->assertExitCode(0);
            $this->assertFileExists($verifyDatabasePath);
        } finally {
            File::deleteDirectory($backupRoot);
            File::deleteDirectory($verifyWorkingPath);
            File::deleteDirectory(dirname($verifyDatabasePath));
            File::delete($databasePath);
        }
    }
}
