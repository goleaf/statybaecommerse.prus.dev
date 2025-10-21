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

    public function handle(): int
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

            $metadataRepositories = $metadata['repositories'] ?? [];
            $repositoryRegistry = $metadataRepositories !== []
                ? RepositoryRegistry::fromDefinitions($this->container(), $metadataRepositories)
                : RepositoryRegistry::fromConfig($this->container());
            $expectedCounts = $metadata['counts'] ?? [];

            if ($repositoryRegistry->isEmpty()) {
                $this->components->warn('No repository counts were recorded in the backup metadata.');
            } else {
                $actualCounts = $repositoryRegistry->counts($connectionName);
                $results = [];

                $labels = array_unique(array_merge(
                    array_keys($repositoryRegistry->definitions()),
                    array_keys($expectedCounts),
                ));

                foreach ($labels as $label) {
                    $expected = $expectedCounts[$label] ?? null;
                    $actualCount = $actualCounts[$label] ?? null;

                    if ($actualCount === null) {
                        throw new RuntimeException(sprintf('Backup repository [%s] is not available for verification.', $label));
                    }

                    $this->compareCounts($label, $expected, $actualCount);

                    $results[$label] = [
                        'expected' => $expected,
                        'actual'   => $actualCount,
                    ];
                }

                foreach ($results as $label => $comparison) {
                    $this->components->twoColumnDetail(
                        sprintf('%s (expected/actual)', Str::headline($label)),
                        sprintf(
                            '%s / %s',
                            $comparison['expected'] !== null ? (string) $comparison['expected'] : 'n/a',
                            (string) $comparison['actual'],
                        ),
                    );
                }
            }

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
            static fn ($path): bool => is_string($path) && File::isDirectory($path),
        ));

        if ($directories === []) {
            return null;
        }

        /** @var array<int, string> $directories */
        rsort($directories);

        return $directories[0] ?? null;
    }

    /**
     * @return array{
     *     artifacts: array{
     *         database: array{filename: string, checksum?: string|null, driver?: string|null},
     *         media: array{filename: string, checksum?: string|null}
     *     },
     *     connection?: array{driver?: string|null},
     *     counts?: array<string, int|null>,
     *     repositories?: array<string, string>
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
            if (! is_array($decoded['counts']) || array_is_list($decoded['counts'])) {
                throw new RuntimeException('Backup metadata counts must be an associative array.');
            }

            $countsInfo = [];

            foreach ($decoded['counts'] as $label => $value) {
                if (! is_string($label) || $label === '') {
                    throw new RuntimeException('Backup metadata count keys must be non-empty strings.');
                }

                if ($value !== null && ! is_int($value)) {
                    throw new RuntimeException(sprintf('Backup metadata count for [%s] must be an integer or null.', $label));
                }

                $countsInfo[$label] = $value;
            }

            if ($countsInfo !== []) {
                $result['counts'] = $countsInfo;
            }
        }

        if (isset($decoded['repositories'])) {
            if (! is_array($decoded['repositories']) || array_is_list($decoded['repositories'])) {
                throw new RuntimeException('Backup metadata repositories must be an associative array.');
            }

            $repositoryInfo = [];

            foreach ($decoded['repositories'] as $label => $class) {
                if (! is_string($label) || $label === '') {
                    throw new RuntimeException('Backup metadata repository keys must be non-empty strings.');
                }

                if (! is_string($class) || $class === '') {
                    throw new RuntimeException(sprintf('Backup metadata repository [%s] must reference a class name.', $label));
                }

                $repositoryInfo[$label] = $class;
            }

            if ($repositoryInfo !== []) {
                $result['repositories'] = $repositoryInfo;
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

        $tarBinary = $this->binary('tar', 'tar');
        $flags = $this->archiveFlags('extract_flags', '-xzf');
        $command = sprintf('%s %s %s -C %s', escapeshellarg($tarBinary), $flags, escapeshellarg($archive), escapeshellarg($destination));
        $process = Process::fromShellCommandline($command);
        $process->setTimeout(null);
        $process->mustRun();
    }

    private function container(): Container
    {
        /** @var Container $container */
        $container = $this->laravel;

        return $container;
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

        $sqliteBinary = $this->binary('sqlite3', 'sqlite3');
        $command = sprintf('%s %s < %s', escapeshellarg($sqliteBinary), escapeshellarg($databasePath), escapeshellarg($artifactPath));
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

        $mysqlBinary = $this->binary('mysql', 'mysql');
        $createCommand = sprintf(
            '%s --host=%s --port=%s --user=%s -e %s',
            escapeshellarg($mysqlBinary),
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
            '%s --host=%s --port=%s --user=%s %s < %s',
            escapeshellarg($mysqlBinary),
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

        $psqlBinary = $this->binary('psql', 'psql');
        $dropCommand = sprintf(
            '%s --host=%s --port=%s --username=%s --command %s',
            escapeshellarg($psqlBinary),
            escapeshellarg($host),
            escapeshellarg($port),
            escapeshellarg($username),
            escapeshellarg(sprintf('DROP DATABASE IF EXISTS "%s";', $database)),
        );

        $createCommand = sprintf(
            '%s --host=%s --port=%s --username=%s --command %s',
            escapeshellarg($psqlBinary),
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
            '%s --host=%s --port=%s --username=%s --dbname=%s -f %s',
            escapeshellarg($psqlBinary),
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

    private function binary(string $key, string $default): string
    {
        $configured = config("backup.binaries.{$key}");

        if (! is_string($configured) || $configured === '') {
            return $default;
        }

        return $configured;
    }

    private function archiveFlags(string $key, string $default): string
    {
        $flags = config("backup.archive.{$key}", $default);

        if (! is_string($flags)) {
            return $default;
        }

        $trimmed = trim($flags);

        return $trimmed !== '' ? $trimmed : $default;
    }
}
