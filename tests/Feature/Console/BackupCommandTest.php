<?php

declare(strict_types=1);

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Symfony\Component\Console\Command\Command;

it('creates and verifies a sqlite backup snapshot with metadata counts', function (): void {
    $uuid = Str::uuid()->toString();
    $backupRoot = storage_path('framework/testing/backups/' . $uuid);
    $snapshotDatabasePath = storage_path('framework/testing/databases/' . $uuid . '/snapshot.sqlite');
    $mediaRoot = storage_path('framework/testing/media/' . $uuid);
    $workingRoot = storage_path('framework/testing/verify-working/' . $uuid);
    $verificationDatabasePath = storage_path('framework/testing/databases/' . $uuid . '/verification.sqlite');

    File::ensureDirectoryExists($backupRoot);
    File::ensureDirectoryExists(dirname($snapshotDatabasePath));
    File::ensureDirectoryExists($mediaRoot);
    File::ensureDirectoryExists(dirname($verificationDatabasePath));

    File::put($snapshotDatabasePath, '');

    File::put($mediaRoot . '/example.txt', 'media');

    config()->set('backup.media_paths', [$mediaRoot]);
    config()->set('backup.storage_path', $backupRoot);
    config()->set('backup.connection', 'backup_snapshot');
    config()->set('backup.repositories', [
        'users'    => \App\Support\Repositories\UserRepository::class,
        'products' => \App\Support\Repositories\ProductRepository::class,
    ]);
    config()->set('backup.verify.connection_name', 'backup_verify');
    config()->set('backup.verify.working_path', $workingRoot);
    config()->set('backup.verify.connection', [
        'driver'                  => 'sqlite',
        'database'                => $verificationDatabasePath,
        'prefix'                  => '',
        'foreign_key_constraints' => true,
    ]);

    config()->set('database.connections.backup_snapshot', [
        'driver'                  => 'sqlite',
        'database'                => $snapshotDatabasePath,
        'prefix'                  => '',
        'foreign_key_constraints' => true,
    ]);

    DB::purge('backup_snapshot');

    Schema::connection('backup_snapshot')->create('users', function (Blueprint $table): void {
        $table->id();
        $table->string('name');
        $table->string('email')->unique();
        $table->string('password');
        $table->timestamps();
    });

    Schema::connection('backup_snapshot')->create('products', function (Blueprint $table): void {
        $table->id();
        $table->string('name');
        $table->decimal('price', 8, 2);
        $table->timestamps();
    });

    DB::connection('backup_snapshot')->table('users')->insert([
        ['name' => 'Ada Lovelace', 'email' => 'ada@example.test', 'password' => bcrypt('secret'), 'created_at' => now(), 'updated_at' => now()],
        ['name' => 'Alan Turing', 'email' => 'alan@example.test', 'password' => bcrypt('secret'), 'created_at' => now(), 'updated_at' => now()],
    ]);

    DB::connection('backup_snapshot')->table('products')->insert([
        ['name' => 'Concrete Mix', 'price' => 49.99, 'created_at' => now(), 'updated_at' => now()],
        ['name' => 'Safety Helmet', 'price' => 19.50, 'created_at' => now(), 'updated_at' => now()],
        ['name' => 'Laser Measure', 'price' => 89.00, 'created_at' => now(), 'updated_at' => now()],
    ]);

    try {
        $prepareExitCode = Artisan::call('backup:prepare', [
            '--connection'   => 'backup_snapshot',
            '--storage-path' => $backupRoot,
        ]);

        expect($prepareExitCode)->toBe(Command::SUCCESS);

        $directories = collect(File::directories($backupRoot))->sort()->values();
        expect($directories)->toHaveCount(1);

        $latestBackupPath = $directories->first();
        $metadataPath = $latestBackupPath . '/metadata.json';

        expect(File::exists($metadataPath))->toBeTrue();

        /** @var array<string, mixed> $metadata */
        $metadata = json_decode(File::get($metadataPath), true, 512, JSON_THROW_ON_ERROR);

        expect($metadata['connection'])->toMatchArray([
            'name'   => 'backup_snapshot',
            'driver' => 'sqlite',
        ]);

        expect($metadata['counts'])->toMatchArray([
            'users'    => 2,
            'products' => 3,
        ]);

        expect($metadata['repositories'])->toBe([
            'users'    => \App\Support\Repositories\UserRepository::class,
            'products' => \App\Support\Repositories\ProductRepository::class,
        ]);

        expect($metadata['artifacts']['database']['filename'])->toBe('database.sqlite');
        expect($metadata['artifacts']['database']['driver'])->toBe('sqlite');
        expect($metadata['artifacts']['media']['filename'])->toBe('media.tar.gz');
        expect($metadata['media_paths'])->toContain($mediaRoot);

        $verifyExitCode = Artisan::call('backup:verify', [
            '--storage-path' => $backupRoot,
            '--working-path' => $workingRoot,
            '--connection'   => 'backup_verify',
        ]);

        expect($verifyExitCode)->toBe(Command::SUCCESS);
        expect(File::exists($verificationDatabasePath))->toBeTrue();
    } finally {
        File::deleteDirectory($backupRoot);
        File::deleteDirectory($mediaRoot);
        File::deleteDirectory($workingRoot);
        File::deleteDirectory(dirname($snapshotDatabasePath));
    }
});
