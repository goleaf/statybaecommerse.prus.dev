<?php

declare(strict_types=1);

namespace Tests\Feature;

use Carbon\CarbonImmutable;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

final class BackupCommandTest extends TestCase
{
    public function test_backup_prepare_and_verify_workflow(): void
    {
        $storageRoot = storage_path('framework/testing/backups');
        $workingPath = storage_path('framework/testing/backup-verify-workdir');
        $backupDatabasePath = storage_path('framework/testing/backup-database.sqlite');
        $verifyDatabasePath = storage_path('framework/testing/verify-database.sqlite');

        File::deleteDirectory($storageRoot);
        File::deleteDirectory($workingPath);
        File::delete($backupDatabasePath);
        File::delete($verifyDatabasePath);
        File::ensureDirectoryExists(dirname($backupDatabasePath));
        File::ensureDirectoryExists(dirname($verifyDatabasePath));

        config([
            'backup.storage_path'           => $storageRoot,
            'backup.connection'             => 'backup_testing',
            'backup.media_paths'            => [],
            'backup.verify.working_path'    => $workingPath,
            'backup.verify.connection_name' => 'verify_testing',
            'backup.verify.connection'      => [
                'driver'                  => 'sqlite',
                'database'                => $verifyDatabasePath,
                'prefix'                  => '',
                'foreign_key_constraints' => true,
            ],
            'database.connections.backup_testing' => [
                'driver'                  => 'sqlite',
                'database'                => $backupDatabasePath,
                'prefix'                  => '',
                'foreign_key_constraints' => true,
            ],
            'database.connections.verify_testing' => [
                'driver'                  => 'sqlite',
                'database'                => $verifyDatabasePath,
                'prefix'                  => '',
                'foreign_key_constraints' => true,
            ],
        ]);

        $now = CarbonImmutable::now();

        Schema::connection('backup_testing')->dropIfExists('products');
        Schema::connection('backup_testing')->dropIfExists('users');

        Schema::connection('backup_testing')->create('users', static function (Blueprint $table): void {
            $table->increments('id');
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamps();
        });

        Schema::connection('backup_testing')->create('products', static function (Blueprint $table): void {
            $table->increments('id');
            $table->string('name');
            $table->unsignedInteger('price');
            $table->timestamps();
        });

        DB::connection('backup_testing')->table('users')->insert([
            [
                'name'       => 'Ada Lovelace',
                'email'      => 'ada@example.com',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name'       => 'Grace Hopper',
                'email'      => 'grace@example.com',
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);

        DB::connection('backup_testing')->table('products')->insert([
            [
                'name'       => 'First Product',
                'price'      => 1234,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name'       => 'Second Product',
                'price'      => 5678,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);

        $this->artisan('backup:prepare')
            ->assertExitCode(0);

        $directories = File::directories($storageRoot);
        $this->assertCount(1, $directories);

        $backupDirectory = $directories[0];

        $this->assertFileExists($backupDirectory . '/database.sqlite');
        $this->assertFileExists($backupDirectory . '/media.empty');
        $this->assertFileExists($backupDirectory . '/metadata.json');

        $metadata = json_decode(File::get($backupDirectory . '/metadata.json'), true, 512, JSON_THROW_ON_ERROR);

        $this->assertSame(2, $metadata['counts']['users']);
        $this->assertSame(2, $metadata['counts']['products']);
        $this->assertSame('sqlite', $metadata['artifacts']['database']['driver']);
        $this->assertSame('media.empty', $metadata['artifacts']['media']['filename']);

        $this->artisan('backup:verify')
            ->assertExitCode(0);

        $this->assertSame(2, DB::connection('verify_testing')->table('users')->count());
        $this->assertSame(2, DB::connection('verify_testing')->table('products')->count());

        $this->assertDirectoryDoesNotExist($workingPath);
    }
}
