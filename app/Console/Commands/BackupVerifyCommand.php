<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Support\Backup\RepositoryRegistry;
use Illuminate\Console\Command;
use Illuminate\Contracts\Container\Container;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use JsonException;
use RuntimeException;
use Throwable;

final class BackupVerifyCommand extends Command
{
    protected $signature = <<<'SIGNATURE'
        backup:verify
            {--storage-path= : Directory containing prepared backup artifacts}
            {--working-path= : Temporary directory used for verification}
            {--connection= : Database connection used for verification}
            {--keep-working : Preserve the working directory after verification}
    SIGNATURE;

    protected $description = 'Verify a prepared backup artifact against an isolated database connection.';

    public function handle(): int
    {
        $container = $this->container();

        $storagePath = $this->optionString('storage-path', (string) config('backup.storage_path', storage_path('app/backups')));
        $workingPath = $this->optionString('working-path', (string) config('backup.verify.working_path', storage_path('app/backup-verify')));
        $connection = $this->optionString('connection', (string) config('backup.verify.connection_name', 'backup-verify'));
        $keepWorking = (bool) $this->option('keep-working');

        if ($storagePath === '') {
            $this->components->error('A storage path is required to verify backups.');

            return self::FAILURE;
        }

        if ($connection === '') {
            $this->components->error('A verification database connection must be provided.');

            return self::FAILURE;
        }

        /** @var array<string, mixed>|null $connectionConfig */
        $connectionConfig = config('backup.verify.connection');

        if (! is_array($connectionConfig) || array_is_list($connectionConfig)) {
            $this->components->error('Backup verification connection configuration is invalid.');

            return self::FAILURE;
        }

        config(["database.connections.{$connection}" => $connectionConfig]);

        $backupPath = $this->findLatestBackupDirectory($storagePath);

        if ($backupPath === null) {
            $this->components->error('No backup artifacts were found to verify.');

            return self::FAILURE;
        }

        File::ensureDirectoryExists($backupPath);

        File::deleteDirectory($workingPath);
        File::ensureDirectoryExists($workingPath);

        try {
            $metadata = $this->readMetadata($backupPath);

            $databaseInfo = $metadata['artifacts']['database'] ?? [];
            $mediaInfo = $metadata['artifacts']['media'] ?? [];

            $databaseArtifact = $backupPath . DIRECTORY_SEPARATOR . $this->assertFilename($databaseInfo, 'database');
            $mediaArtifact = $backupPath . DIRECTORY_SEPARATOR . $this->assertFilename($mediaInfo, 'media');

            $this->assertChecksum($databaseArtifact, $databaseInfo['checksum'] ?? null, 'database');
            $this->assertChecksum($mediaArtifact, $mediaInfo['checksum'] ?? null, 'media');

            $driver = $databaseInfo['driver'] ?? $connectionConfig['driver'] ?? null;

            if ($driver !== 'sqlite') {
                throw new RuntimeException(sprintf('Only sqlite verification is supported, received [%s].', (string) $driver));
            }

            $targetDatabasePath = $connectionConfig['database'] ?? null;

            if (! is_string($targetDatabasePath) || $targetDatabasePath === '') {
                throw new RuntimeException('Verification database path is not configured.');
            }

            File::ensureDirectoryExists(dirname($targetDatabasePath));
            File::copy($databaseArtifact, $targetDatabasePath);

            DB::purge($connection);
            DB::reconnect($connection);

            $registry = array_key_exists('repositories', $metadata)
                ? RepositoryRegistry::fromDefinitions($container, (array) $metadata['repositories'])
                : RepositoryRegistry::fromConfig($container);

            $expectedCounts = [];

            if (isset($metadata['counts']) && is_array($metadata['counts'])) {
                foreach ($metadata['counts'] as $label => $value) {
                    if (is_string($label) && $label !== '') {
                        $expectedCounts[$label] = is_int($value) ? $value : (is_numeric($value) ? (int) $value : null);
                    }
                }
            }

            $actualCounts = $registry->counts($connection);

            foreach ($expectedCounts as $label => $expected) {
                $actual = $actualCounts[$label] ?? null;

                if ($expected !== null && $actual !== $expected) {
                    throw new RuntimeException(sprintf(
                        'Repository [%s] expected %s records but found %s.',
                        $label,
                        $expected,
                        $actual ?? 'unknown'
                    ));
                }
            }

            $this->components->info('Backup verification completed successfully.');

            return self::SUCCESS;
        } catch (Throwable $exception) {
            $this->components->error($exception->getMessage());
            logger()->error('backup.verify_failed', ['exception' => $exception]);

            return self::FAILURE;
        } finally {
            if (! $keepWorking) {
                File::deleteDirectory($workingPath);
            }
        }
    }

    private function findLatestBackupDirectory(string $storageRoot): ?string
    {
        if (! File::exists($storageRoot)) {
            return null;
        }

        $directories = array_filter(File::directories($storageRoot), static fn ($path): bool => is_string($path));

        if ($directories === []) {
            return null;
        }

        rsort($directories);

        return $directories[0] ?? null;
    }

    /**
     * @return array<string, mixed>
     */
    private function readMetadata(string $backupPath): array
    {
        $metadataPath = $backupPath . DIRECTORY_SEPARATOR . 'metadata.json';

        if (! File::exists($metadataPath)) {
            throw new RuntimeException('Backup metadata file is missing.');
        }

        try {
            $contents = File::get($metadataPath);
            $metadata = json_decode($contents, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $exception) {
            throw new RuntimeException('Failed to decode backup metadata.', 0, $exception);
        }

        if (! is_array($metadata) || array_is_list($metadata)) {
            throw new RuntimeException('Backup metadata file is malformed.');
        }

        return $metadata;
    }

    private function assertChecksum(string $path, ?string $expected, string $label): void
    {
        if ($expected === null || $expected === '') {
            return;
        }

        $hash = hash_file('sha256', $path);

        if ($hash === false) {
            throw new RuntimeException(sprintf('Unable to calculate checksum for %s artifact.', $label));
        }

        if (! hash_equals($expected, $hash)) {
            throw new RuntimeException(sprintf('Checksum mismatch for %s artifact.', $label));
        }
    }

    /**
     * @param  array<string, mixed>  $info
     */
    private function assertFilename(array $info, string $label): string
    {
        $filename = $info['filename'] ?? null;

        if (! is_string($filename) || $filename === '') {
            throw new RuntimeException(sprintf('The %s artifact filename is missing from metadata.', $label));
        }

        return $filename;
    }

    private function optionString(string $name, ?string $default = null): string
    {
        $value = $this->option($name);

        if (is_string($value) && $value !== '') {
            return $value;
        }

        return $default ?? '';
    }

    private function container(): Container
    {
        return $this->laravel ?? app();
    }
}
