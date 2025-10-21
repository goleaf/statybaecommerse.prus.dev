<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Support\Repositories\ProductRepository;
use App\Support\Repositories\UserRepository;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use JsonException;
use RuntimeException;
use Symfony\Component\Process\Process;
use Throwable;

final class BackupVerifyCommand extends Command
{
    protected $signature = 'backup:verify
                            {--storage-path= : Override the backup storage root}
                            {--working-path= : Override the temporary extraction path}
                            {--connection= : Database connection name to use during verification}
                            {--keep-working : Do not clean up the working directory after verification}';

    protected $description = 'Restore the most recent backup into an ephemeral database and run sanity checks.';

    public function handle(UserRepository $userRepository, ProductRepository $productRepository): int
    {
        $defaultStorage = config('backup.storage_path', storage_path('app/backups'));
        $storageRoot = $this->normalizePath($this->optionString('storage-path', is_string($defaultStorage) ? $defaultStorage : storage_path('app/backups')));

        $defaultWorking = config('backup.verify.working_path', storage_path('app/backup-verify'));
        $workingPath = $this->normalizePath($this->optionString('working-path', is_string($defaultWorking) ? $defaultWorking : storage_path('app/backup-verify')));

        $defaultConnectionName = config('backup.verify.connection_name', 'backup-verify');
        $connectionName = $this->optionString('connection', is_string($defaultConnectionName) ? $defaultConnectionName : 'backup-verify');
        $keepWorking = (bool) $this->option('keep-working');

        try {
            $latestBackup = $this->findLatestBackupDirectory($storageRoot);

            if ($latestBackup === null) {
                throw new RuntimeException('No backups were found to verify.');
            }

            $metadata = $this->readMetadata($latestBackup);

            $databaseArtifact = $latestBackup . '/' . $metadata['artifacts']['database']['filename'];
            $mediaArtifact = $latestBackup . '/' . $metadata['artifacts']['media']['filename'];

            $this->assertChecksum($databaseArtifact, $metadata['artifacts']['database']['checksum'] ?? null, 'database');
            $this->assertChecksum($mediaArtifact, $metadata['artifacts']['media']['checksum'] ?? null, 'media');

            $this->components->info('Extracting media archive...');
            $mediaExtractionPath = $workingPath . '/media';
            $this->prepareWorkingDirectory($workingPath, $mediaExtractionPath);
            $this->extractArchive($mediaArtifact, $mediaExtractionPath);

            $this->components->info('Restoring database snapshot...');
            $connectionConfig = $this->resolveVerificationConnectionConfig($connectionName);
            $driver = $metadata['artifacts']['database']['driver'] ?? $metadata['connection']['driver'] ?? $connectionConfig['driver'] ?? null;

            if (! is_string($driver) || $driver === '') {
                throw new RuntimeException('Unable to determine database driver for verification.');
            }

            config(["database.connections.{$connectionName}" => $connectionConfig]);
            DB::purge($connectionName);

            $this->restoreDatabase($driver, $connectionConfig, $databaseArtifact);

            DB::purge($connectionName);
            DB::reconnect($connectionName);

            $this->components->info('Running sanity checks...');
            $userCount = $userRepository->count($connectionName);
            $productCount = $productRepository->count($connectionName);

            $expectedCounts = $metadata['counts'] ?? [];
            $expectedUsers = $expectedCounts['users'] ?? null;
            $expectedProducts = $expectedCounts['products'] ?? null;

            $this->compareCounts('users', $expectedUsers, $userCount);
            $this->compareCounts('products', $expectedProducts, $productCount);

            $this->components->twoColumnDetail(
                'Users (expected/actual)',
                sprintf('%s / %s', $expectedUsers !== null ? (string) $expectedUsers : 'n/a', (string) $userCount),
            );
            $this->components->twoColumnDetail(
                'Products (expected/actual)',
                sprintf('%s / %s', $expectedProducts !== null ? (string) $expectedProducts : 'n/a', (string) $productCount),
            );

            $this->components->info('Backup verification completed successfully.');

            return self::SUCCESS;
        } catch (Throwable $exception) {
            $this->components->error($exception->getMessage());

            return self::FAILURE;
        } finally {
            if (! $keepWorking) {
                File::deleteDirectory($workingPath);
            }
        }
    }

    private function normalizePath(string $path): string
    {
        if ($path === '') {
            return $path;
        }

        if (Str::startsWith($path, DIRECTORY_SEPARATOR) || preg_match('/^[A-Za-z]:\\\\/', $path) === 1) {
            return $path;
        }

        return base_path($path);
    }

    private function findLatestBackupDirectory(string $storageRoot): ?string
    {
        if (! File::exists($storageRoot)) {
            return null;
        }

        $directories = array_values(array_filter(
            File::directories($storageRoot),
            static function ($path): bool {
                return is_string($path) && File::isDirectory($path);
            },
        ));

        if ($directories === []) {
            return null;
        }

        rsort($directories);

        /** @var string $latest */
        $latest = $directories[0];

        return $latest;
    }

    /**
     * @return array{
     *     artifacts: array{
     *         database: array{filename: string, checksum?: string|null, driver?: string|null},
     *         media: array{filename: string, checksum?: string|null}
     *     },
     *     connection?: array{driver?: string|null},
     *     counts?: array{users?: int|null, products?: int|null}
     * }
     */
    private function readMetadata(string $backupPath): array
    {
        $metadataPath = $backupPath . '/metadata.json';

        if (! File::exists($metadataPath)) {
            throw new RuntimeException('Backup metadata file is missing.');
        }

        try {
            /** @var array<string, mixed> $decoded */
            $decoded = json_decode(File::get($metadataPath), true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $exception) {
            throw new RuntimeException('Failed to parse backup metadata: ' . $exception->getMessage(), 0, $exception);
        }

        if (! isset($decoded['artifacts']) || ! is_array($decoded['artifacts'])) {
            throw new RuntimeException('Backup metadata is missing artifact information.');
        }

        $artifacts = $decoded['artifacts'];

        if (! isset($artifacts['database']) || ! is_array($artifacts['database'])) {
            throw new RuntimeException('Backup metadata is missing database artifact details.');
        }

        if (! isset($artifacts['media']) || ! is_array($artifacts['media'])) {
            throw new RuntimeException('Backup metadata is missing media artifact details.');
        }

        $database = $artifacts['database'];
        $media = $artifacts['media'];

        $databaseFilename = $database['filename'] ?? null;
        $mediaFilename = $media['filename'] ?? null;

        if (! is_string($databaseFilename) || $databaseFilename === '') {
            throw new RuntimeException('Backup metadata database filename is invalid.');
        }

        if (! is_string($mediaFilename) || $mediaFilename === '') {
            throw new RuntimeException('Backup metadata media filename is invalid.');
        }

        $databaseInfo = ['filename' => $databaseFilename];

        if (array_key_exists('checksum', $database)) {
            $checksum = $database['checksum'];

            if ($checksum !== null && ! is_string($checksum)) {
                throw new RuntimeException('Backup metadata database checksum must be a string or null.');
            }

            $databaseInfo['checksum'] = $checksum;
        }

        if (array_key_exists('driver', $database)) {
            $driver = $database['driver'];

            if ($driver !== null && ! is_string($driver)) {
                throw new RuntimeException('Backup metadata database driver must be a string or null.');
            }

            $databaseInfo['driver'] = $driver;
        }

        $mediaInfo = ['filename' => $mediaFilename];

        if (array_key_exists('checksum', $media)) {
            $checksum = $media['checksum'];

            if ($checksum !== null && ! is_string($checksum)) {
                throw new RuntimeException('Backup metadata media checksum must be a string or null.');
            }

            $mediaInfo['checksum'] = $checksum;
        }

        $result = [
            'artifacts' => [
                'database' => $databaseInfo,
                'media'    => $mediaInfo,
            ],
        ];

        if (isset($decoded['connection'])) {
            if (! is_array($decoded['connection'])) {
                throw new RuntimeException('Backup metadata connection details must be an array.');
            }

            $connectionInfo = [];

            if (array_key_exists('driver', $decoded['connection'])) {
                $driver = $decoded['connection']['driver'];

                if ($driver !== null && ! is_string($driver)) {
                    throw new RuntimeException('Backup metadata connection driver must be a string or null.');
                }

                $connectionInfo['driver'] = $driver;
            }

            if ($connectionInfo !== []) {
                $result['connection'] = $connectionInfo;
            }
        }

        if (isset($decoded['counts'])) {
            if (! is_array($decoded['counts'])) {
                throw new RuntimeException('Backup metadata counts must be an array.');
            }

            $countsInfo = [];

            if (array_key_exists('users', $decoded['counts'])) {
                $users = $decoded['counts']['users'];

                if ($users !== null && ! is_int($users)) {
                    throw new RuntimeException('Backup metadata user count must be an integer or null.');
                }

                $countsInfo['users'] = $users;
            }

            if (array_key_exists('products', $decoded['counts'])) {
                $products = $decoded['counts']['products'];

                if ($products !== null && ! is_int($products)) {
                    throw new RuntimeException('Backup metadata product count must be an integer or null.');
                }

                $countsInfo['products'] = $products;
            }

            if ($countsInfo !== []) {
                $result['counts'] = $countsInfo;
            }
        }

        return $result;
    }

    private function assertChecksum(string $path, ?string $expectedHash, string $label): void
    {
        if (! File::exists($path)) {
            throw new RuntimeException(sprintf('The %s artifact [%s] is missing.', $label, $path));
        }

        if ($expectedHash === null) {
            $this->components->warn(sprintf('No checksum recorded for %s artifact.', $label));

            return;
        }

        $actual = hash_file('sha256', $path);

        if ($actual === false) {
            throw new RuntimeException(sprintf('Failed to compute checksum for %s artifact.', $label));
        }

        if (! hash_equals($expectedHash, $actual)) {
            throw new RuntimeException(sprintf('Checksum mismatch for %s artifact.', $label));
        }
    }

    private function prepareWorkingDirectory(string $workingPath, string $mediaExtractionPath): void
    {
        File::deleteDirectory($workingPath);
        File::ensureDirectoryExists($workingPath);
        File::ensureDirectoryExists($mediaExtractionPath);
    }

    private function extractArchive(string $archive, string $destination): void
    {
        if (Str::endsWith($archive, '.empty')) {
            $this->components->warn('Media archive was a placeholder - skipping extraction.');

            return;
        }

        $command = sprintf('tar -xzf %s -C %s', escapeshellarg($archive), escapeshellarg($destination));
        $process = Process::fromShellCommandline($command);
        $process->setTimeout(null);
        $process->mustRun();
    }

    /**
     * @return array<string, mixed>
     */
    private function resolveVerificationConnectionConfig(string $connectionName): array
    {
        $configured = config('backup.verify.connection');

        if (is_array($configured) && $configured !== []) {
            if (array_is_list($configured)) {
                throw new RuntimeException('Verification connection configuration must be an associative array.');
            }

            /** @var array<string, mixed> $configuredArray */
            $configuredArray = $configured;

            return $configuredArray;
        }

        $fallback = config("database.connections.{$connectionName}");

        if (! is_array($fallback) || array_is_list($fallback)) {
            throw new RuntimeException("Verification connection [{$connectionName}] is not configured.");
        }

        /** @var array<string, mixed> $fallbackArray */
        $fallbackArray = $fallback;

        return $fallbackArray;
    }

    /**
     * @param array<string, mixed> $connectionConfig
     */
    private function restoreDatabase(string $driver, array $connectionConfig, string $artifactPath): void
    {
        match ($driver) {
            'sqlite' => $this->restoreSqliteDatabase($connectionConfig, $artifactPath),
            'mysql', 'mariadb' => $this->restoreMysqlDatabase($connectionConfig, $artifactPath),
            'pgsql' => $this->restorePostgresDatabase($connectionConfig, $artifactPath),
            default => throw new RuntimeException("Verification for driver [{$driver}] is not supported."),
        };
    }

    /**
     * @param array<string, mixed> $connectionConfig
     */
    private function restoreSqliteDatabase(array $connectionConfig, string $artifactPath): void
    {
        $databasePath = $connectionConfig['database'] ?? null;

        if (! is_string($databasePath) || $databasePath === '') {
            throw new RuntimeException('SQLite verification database path is not configured.');
        }

        File::ensureDirectoryExists(dirname($databasePath));
        File::delete($databasePath);

        if (Str::endsWith($artifactPath, '.sqlite')) {
            File::copy($artifactPath, $databasePath);

            return;
        }

        $command = sprintf('sqlite3 %s < %s', escapeshellarg($databasePath), escapeshellarg($artifactPath));
        $process = Process::fromShellCommandline($command);
        $process->setTimeout(null);
        $process->mustRun();
    }

    /**
     * @param array<string, mixed> $connectionConfig
     */
    private function restoreMysqlDatabase(array $connectionConfig, string $artifactPath): void
    {
        $database = $this->connectionValue($connectionConfig, 'database');
        $host = $this->connectionValue($connectionConfig, 'host', '127.0.0.1') ?? '127.0.0.1';
        $port = $this->connectionValue($connectionConfig, 'port', '3306') ?? '3306';
        $username = $this->connectionValue($connectionConfig, 'username');
        $password = $this->connectionValue($connectionConfig, 'password', '') ?? '';

        if ($database === null || $database === '') {
            throw new RuntimeException('MySQL verification database is not configured.');
        }

        if ($username === null || $username === '') {
            throw new RuntimeException('MySQL verification username is not configured.');
        }

        $createCommand = sprintf(
            'mysql --host=%s --port=%s --user=%s -e %s',
            escapeshellarg($host),
            escapeshellarg($port),
            escapeshellarg($username),
            escapeshellarg(sprintf('DROP DATABASE IF EXISTS `%s`; CREATE DATABASE `%s`;', $database, $database)),
        );

        $mysqlEnv = $password === '' ? [] : ['MYSQL_PWD' => $password];

        $createProcess = Process::fromShellCommandline($createCommand, null, $mysqlEnv);
        $createProcess->setTimeout(null);
        $createProcess->mustRun();

        $importCommand = sprintf(
            'mysql --host=%s --port=%s --user=%s %s < %s',
            escapeshellarg($host),
            escapeshellarg($port),
            escapeshellarg($username),
            escapeshellarg($database),
            escapeshellarg($artifactPath),
        );

        $importProcess = Process::fromShellCommandline($importCommand, null, $mysqlEnv);
        $importProcess->setTimeout(null);
        $importProcess->mustRun();
    }

    /**
     * @param array<string, mixed> $connectionConfig
     */
    private function restorePostgresDatabase(array $connectionConfig, string $artifactPath): void
    {
        $database = $this->connectionValue($connectionConfig, 'database');
        $host = $this->connectionValue($connectionConfig, 'host', '127.0.0.1') ?? '127.0.0.1';
        $port = $this->connectionValue($connectionConfig, 'port', '5432') ?? '5432';
        $username = $this->connectionValue($connectionConfig, 'username');
        $password = $this->connectionValue($connectionConfig, 'password', '') ?? '';

        if ($database === null || $database === '') {
            throw new RuntimeException('PostgreSQL verification database is not configured.');
        }

        if ($username === null || $username === '') {
            throw new RuntimeException('PostgreSQL verification username is not configured.');
        }

        $dropCommand = sprintf(
            'psql --host=%s --port=%s --username=%s --command %s',
            escapeshellarg($host),
            escapeshellarg($port),
            escapeshellarg($username),
            escapeshellarg(sprintf('DROP DATABASE IF EXISTS "%s";', $database)),
        );

        $createCommand = sprintf(
            'psql --host=%s --port=%s --username=%s --command %s',
            escapeshellarg($host),
            escapeshellarg($port),
            escapeshellarg($username),
            escapeshellarg(sprintf('CREATE DATABASE "%s";', $database)),
        );

        $env = $password === '' ? [] : ['PGPASSWORD' => $password];

        $dropProcess = Process::fromShellCommandline($dropCommand, null, $env);
        $dropProcess->setTimeout(null);
        $dropProcess->mustRun();

        $createProcess = Process::fromShellCommandline($createCommand, null, $env);
        $createProcess->setTimeout(null);
        $createProcess->mustRun();

        $importCommand = sprintf(
            'psql --host=%s --port=%s --username=%s --dbname=%s -f %s',
            escapeshellarg($host),
            escapeshellarg($port),
            escapeshellarg($username),
            escapeshellarg($database),
            escapeshellarg($artifactPath),
        );

        $importProcess = Process::fromShellCommandline($importCommand, null, $env);
        $importProcess->setTimeout(null);
        $importProcess->mustRun();
    }

    /**
     * @param array<string, mixed> $config
     */
    private function connectionValue(array $config, string $key, ?string $default = null): ?string
    {
        if (! array_key_exists($key, $config)) {
            return $default;
        }

        $value = $config[$key];

        if ($value === null) {
            return null;
        }

        if (is_scalar($value)) {
            return (string) $value;
        }

        throw new RuntimeException(sprintf('Verification connection value for [%s] must be a scalar or null.', $key));
    }

    private function compareCounts(string $label, ?int $expected, int $actual): void
    {
        if ($expected === null) {
            $this->components->warn(sprintf('No expected count recorded for %s.', $label));

            return;
        }

        if ($expected !== $actual) {
            throw new RuntimeException(sprintf('Sanity check failed for %s count. Expected %d, found %d.', $label, $expected, $actual));
        }
    }

    private function optionString(string $name, string $default): string
    {
        $value = $this->option($name);

        return is_string($value) && $value !== '' ? $value : $default;
    }
}
