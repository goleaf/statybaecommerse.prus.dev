<?php

declare(strict_types=1);

namespace Tests\Feature\Console;

use App\Support\Repositories\ProductRepository;
use App\Support\Repositories\UserRepository;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

final class BackupCommandTest extends TestCase
{
    public function test_backup_prepare_and_verify_round_trip(): void
    {
        $storagePath = storage_path('framework/testing/backups');
        $snapshotDirectory = storage_path('framework/testing/backup-snapshot');
        $snapshotPath = $snapshotDirectory . '/database.sqlite';
        $mediaPath = storage_path('framework/testing/backup-media');
        $workingPath = storage_path('framework/testing/backup-working');
        $verificationDatabase = $workingPath . '/database.sqlite';

        File::deleteDirectory($storagePath);
        File::deleteDirectory($snapshotDirectory);
        File::deleteDirectory($mediaPath);
        File::deleteDirectory($workingPath);

        File::ensureDirectoryExists($snapshotDirectory);
        File::put($snapshotPath, '');
        File::ensureDirectoryExists($mediaPath);
        File::put($mediaPath . '/placeholder.txt', 'media');

        Config::set('backup.storage_path', $storagePath);
        Config::set('backup.media_paths', [$mediaPath]);
        Config::set('backup.connection', 'backup-snapshot');
        Config::set('backup.repositories', [
            'users'    => UserRepository::class,
            'products' => ProductRepository::class,
        ]);

        Config::set('database.connections.backup-snapshot', [
            'driver'                  => 'sqlite',
            'database'                => $snapshotPath,
            'prefix'                  => '',
            'foreign_key_constraints' => true,
        ]);

        Config::set('backup.verify.connection_name', 'backup-verify-test');
        Config::set('backup.verify.working_path', $workingPath);
        Config::set('backup.verify.connection', [
            'driver'                  => 'sqlite',
            'database'                => $verificationDatabase,
            'prefix'                  => '',
            'foreign_key_constraints' => true,
        ]);

        Schema::connection('backup-snapshot')->dropIfExists('users');
        Schema::connection('backup-snapshot')->dropIfExists('products');

        Schema::connection('backup-snapshot')->create('users', function (Blueprint $table): void {
            $table->increments('id');
            $table->string('name');
            $table->timestamps();
        });

        Schema::connection('backup-snapshot')->create('products', function (Blueprint $table): void {
            $table->increments('id');
            $table->string('name');
            $table->decimal('price', 8, 2);
            $table->timestamps();
        });

        DB::connection('backup-snapshot')->table('users')->insert([
            ['name' => 'Alice', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Bob', 'created_at' => now(), 'updated_at' => now()],
        ]);

        DB::connection('backup-snapshot')->table('products')->insert([
            ['name' => 'Widget', 'price' => 9.99, 'created_at' => now(), 'updated_at' => now()],
        ]);

        self::assertSame(0, Artisan::call('backup:prepare'));

        $directories = File::directories($storagePath);
        self::assertCount(1, $directories);

        $backupPath = $directories[0];
        $metadataPath = $backupPath . '/metadata.json';
        self::assertFileExists($metadataPath);

        /** @var array<string, mixed> $metadata */
        $metadata = json_decode(File::get($metadataPath), true, 512, JSON_THROW_ON_ERROR);

        self::assertSame('backup-snapshot', $metadata['connection']['name']);
        self::assertSame('sqlite', $metadata['connection']['driver']);
        self::assertSame(
            [
                'users'    => UserRepository::class,
                'products' => ProductRepository::class,
            ],
            $metadata['repositories'],
        );

        self::assertSame([
            'users'    => 2,
            'products' => 1,
        ], $metadata['counts']);

        self::assertSame(0, Artisan::call('backup:verify', [
            '--storage-path' => $storagePath,
            '--working-path' => $workingPath,
            '--connection'   => 'backup-verify-test',
        ]));

        $verifiedUsers = DB::connection('backup-verify-test')->table('users')->count();
        $verifiedProducts = DB::connection('backup-verify-test')->table('products')->count();

        self::assertSame(2, $verifiedUsers);
        self::assertSame(1, $verifiedProducts);

        self::assertDirectoryDoesNotExist($workingPath);
    }
}
