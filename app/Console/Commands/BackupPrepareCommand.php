<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Support\Backup\RepositoryRegistry;
use Carbon\CarbonImmutable;
use Illuminate\Console\Command;
use Illuminate\Contracts\Container\Container;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use RuntimeException;
use Symfony\Component\Process\Process;
use Throwable;

final class BackupPrepareCommand extends Command
{
    protected $signature = 'backup:prepare
                            {--connection= : Database connection name to dump}
                            {--storage-path= : Override the backup storage root}
                            {--media-paths=* : Additional media paths to include}
                            {--tag= : Optional suffix for the backup directory}';

    protected $description = 'Create a timestamped backup containing the database dump and media assets.';

    public function handle(): int
    {
        $databaseDefault = config('database.default', 'sqlite');
        $databaseDefaultString = is_string($databaseDefault) ? $databaseDefault : 'sqlite';
        $connectionDefault = config('backup.connection', $databaseDefaultString);
        $connectionName = $this->optionString('connection', is_string($connectionDefault) ? $connectionDefault : $databaseDefaultString);

        $storageDefault = config('backup.storage_path', storage_path('app/backups'));
        $storageRoot = $this->normalizePath($this->optionString('storage-path', is_string($storageDefault) ? $storageDefault : storage_path('app/backups')));

        $extraMediaOption = (array) $this->option('media-paths');
        $additionalMediaPaths = array_values(array_filter($extraMediaOption, static fn ($value): bool => is_string($value)));

        $mediaPaths = $this->resolveMediaPaths($additionalMediaPaths);
        $timestamp = CarbonImmutable::now()->format('Ymd_His');
        $tag = $this->option('tag');
        $directoryName = $tag !== null ? sprintf('%s_%s', $timestamp, Str::slug($tag)) : $timestamp;
        $backupPath = $storageRoot . DIRECTORY_SEPARATOR . $directoryName;

        $this->components->info(sprintf('Starting backup for connection [%s] into %s', $connectionName, $backupPath));

        File::ensureDirectoryExists($backupPath);

        try {
            $databaseConfig = config("database.connections.{$connectionName}");

            if (! is_array($databaseConfig)) {
                throw new RuntimeException("Database connection [{$connectionName}] is not configured.");
            }

            if (array_is_list($databaseConfig)) {
                throw new RuntimeException("Database connection [{$connectionName}] configuration must be an associative array.");
            }

            /** @var array<string, mixed> $databaseConfig */
            $databaseConfig = $databaseConfig;

            if (($databaseConfig['driver'] ?? null) === 'sqlite') {
                // Surface both the configured and active database paths for debugging.
                logger()->info('backup.sqlite_connection', [
                    'connection' => $connectionName,
                    'configured' => $databaseConfig['database'] ?? null,
                    'active'     => DB::connection($connectionName)->getDatabaseName(),
                ]);
            }

            $databaseArtifact = $this->dumpDatabase($connectionName, $databaseConfig, $backupPath);
            $mediaArtifact = $this->archiveMedia($mediaPaths, $backupPath);
            $commitHash = $this->resolveCommitHash();

            $databaseChecksum = hash_file('sha256', $databaseArtifact['path']);
            $mediaChecksum = hash_file('sha256', $mediaArtifact);

            if ($databaseChecksum === false || $mediaChecksum === false) {
                throw new RuntimeException('Failed to compute artifact checksums.');
            }

            $repositoryRegistry = RepositoryRegistry::fromConfig($this->container());
            $repositoryCounts = $repositoryRegistry->counts($connectionName);

            $metadata = [
                'timestamp'  => $timestamp,
                'directory'  => $directoryName,
                'connection' => [
                    'name'   => $connectionName,
                    'driver' => $databaseArtifact['driver'],
                ],
                'commit_hash'  => $commitHash,
                'media_paths'  => $mediaPaths,
                'repositories' => $repositoryRegistry->definitions(),
                'artifacts'    => [
                    'database' => [
                        'filename' => basename($databaseArtifact['path']),
                        'driver'   => $databaseArtifact['driver'],
                        'checksum' => $databaseChecksum,
                    ],
                    'media' => [
                        'filename' => basename($mediaArtifact),
                        'checksum' => $mediaChecksum,
                    ],
                ],
                'counts'       => $repositoryCounts,
                'generated_at' => CarbonImmutable::now()->toIso8601String(),
            ];

            $metadataJson = json_encode($metadata, JSON_PRETTY_PRINT);

            if ($metadataJson === false) {
                throw new RuntimeException('Failed to encode backup metadata.');
            }

            File::put($backupPath . '/metadata.json', $metadataJson);

            $this->components->info('Backup created successfully.');
            $this->newLine();
            $this->components->twoColumnDetail('Database artifact', $databaseArtifact['path']);
            $this->components->twoColumnDetail('Media archive', $mediaArtifact);
            $this->components->twoColumnDetail('Metadata', $backupPath . '/metadata.json');

            foreach ($repositoryCounts as $label => $count) {
                $this->components->twoColumnDetail(
                    sprintf('Records [%s]', Str::headline($label)),
                    (string) $count,
                );
            }

            return self::SUCCESS;
        } catch (Throwable $exception) {
            $this->components->error($exception->getMessage());
            // Record the exception so debugging backup failures in tests is straightforward.
            logger()->error('backup.prepare_failed', ['exception' => $exception]);
            File::deleteDirectory($backupPath);

            return self::FAILURE;
        }
    }

    /**
     * @param  array<int, string>|null $extraMediaPaths
     * @return array<int, string>
     */
    private function resolveMediaPaths(?array $extraMediaPaths): array
    {
        $configured = array_values(array_filter(
            (array) config('backup.media_paths', [storage_path('app/public')]),
            static fn ($value): bool => is_string($value),
        ));

        $candidates = [];

        foreach (array_merge($configured, $extraMediaPaths ?? []) as $path) {
            if ($path === '') {
                continue;
            }

            $candidates[] = $this->normalizePath($path);
        }

        $uniquePaths = array_values(array_unique($candidates));
        $existing = [];

        foreach ($uniquePaths as $candidate) {
            if (File::exists($candidate)) {
                $existing[] = $candidate;

                continue;
            }

            $this->components->warn(sprintf('Media path [%s] does not exist and will be skipped.', $candidate));
        }

        return $existing;
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

    /**
     * @param  array<string, mixed>                $config
     * @return array{path: string, driver: string}
     */
    private function dumpDatabase(string $connection, array $config, string $backupPath): array
    {
        $driver = $this->connectionValue($config, 'driver');

        if ($driver === null || $driver === '') {
            throw new RuntimeException("Database connection [{$connection}] is missing a driver definition.");
        }

        return match ($driver) {
            'sqlite' => $this->dumpSqliteDatabase($connection, $config, $backupPath),
            'mysql', 'mariadb' => $this->dumpMysqlDatabase($config, $backupPath),
            'pgsql' => $this->dumpPostgresDatabase($config, $backupPath),
            default => throw new RuntimeException("Dumping for driver [{$driver}] is not supported."),
        };
    }

    /**
     * @param  array<string, mixed>                $config
     * @return array{path: string, driver: string}
     */
    private function dumpSqliteDatabase(string $connection, array $config, string $backupPath): array
    {
        $databasePath = $config['database'] ?? null;

        if (! is_string($databasePath) || $databasePath === '') {
            throw new RuntimeException('SQLite database path is not configured.');
        }

        // Emit a breadcrumb so failing tests surface the evaluated database path.
        logger()->info('backup.sqlite_source', [
            'database' => $databasePath,
            'exists'   => File::exists($databasePath),
        ]);

        clearstatcache(true, $databasePath);

        $targetPath = $backupPath . '/database.sqlite';

        if (! File::exists($databasePath)) {
            try {
                // Fallback to exporting the active connection via SQLite's VACUUM INTO command.
                DB::connection($connection)->getPdo()->exec(sprintf(
                    "VACUUM INTO '%s'",
                    str_replace("'", "''", $targetPath)
                ));

                logger()->warning('backup.sqlite_vacuum_fallback', [
                    'connection' => $connection,
                    'source'     => $databasePath,
                    'target'     => $targetPath,
                ]);
            } catch (Throwable $exception) {
                throw new FileNotFoundException("SQLite database [{$databasePath}] not found.", previous: $exception);
            }
        } else {
            File::copy($databasePath, $targetPath);
        }

        return [
            'path'   => $targetPath,
            'driver' => 'sqlite',
        ];
    }

    /**
     * @param  array<string, mixed>                $config
     * @return array{path: string, driver: string}
     */
    private function dumpMysqlDatabase(array $config, string $backupPath): array
    {
        $database = $this->connectionValue($config, 'database');
        $host = $this->connectionValue($config, 'host', '127.0.0.1') ?? '127.0.0.1';
        $port = $this->connectionValue($config, 'port', '3306') ?? '3306';
        $username = $this->connectionValue($config, 'username');
        $password = $this->connectionValue($config, 'password', '') ?? '';

        if ($database === null || $database === '') {
            throw new RuntimeException('MySQL database name is not configured.');
        }

        if ($username === null || $username === '') {
            throw new RuntimeException('MySQL username is not configured.');
        }

        $dumpPath = $backupPath . '/database.sql';
        $binary = $this->binary('mysqldump', 'mysqldump');
        $options = $this->commandOptions('backup.dump.mysql.options', '--single-transaction --routines --events');
        $optionsPart = $options === '' ? '' : ' ' . $options;

        $command = sprintf(
            '%s --host=%s --port=%s --user=%s%s %s > %s',
            escapeshellarg($binary),
            escapeshellarg($host),
            escapeshellarg($port),
            escapeshellarg($username),
            $optionsPart,
            escapeshellarg($database),
            escapeshellarg($dumpPath),
        );

        $mysqlEnv = $password === '' ? [] : ['MYSQL_PWD' => $password];

        $process = Process::fromShellCommandline($command, null, $mysqlEnv);
        $process->setTimeout(null);
        $process->mustRun();

        return [
            'path'   => $dumpPath,
            'driver' => 'mysql',
        ];
    }

    /**
     * @param  array<string, mixed>                $config
     * @return array{path: string, driver: string}
     */
    private function dumpPostgresDatabase(array $config, string $backupPath): array
    {
        $database = $this->connectionValue($config, 'database');
        $host = $this->connectionValue($config, 'host', '127.0.0.1') ?? '127.0.0.1';
        $port = $this->connectionValue($config, 'port', '5432') ?? '5432';
        $username = $this->connectionValue($config, 'username');
        $password = $this->connectionValue($config, 'password', '') ?? '';

        if ($database === null || $database === '') {
            throw new RuntimeException('PostgreSQL database name is not configured.');
        }

        if ($username === null || $username === '') {
            throw new RuntimeException('PostgreSQL username is not configured.');
        }

        $dumpPath = $backupPath . '/database.sql';
        $binary = $this->binary('pg_dump', 'pg_dump');
        $options = $this->commandOptions('backup.dump.pgsql.options', '--no-owner --no-privileges');
        $optionsPart = $options === '' ? '' : ' ' . $options;

        $command = sprintf(
            '%s --host=%s --port=%s --username=%s%s %s > %s',
            escapeshellarg($binary),
            escapeshellarg($host),
            escapeshellarg($port),
            escapeshellarg($username),
            $optionsPart,
            escapeshellarg($database),
            escapeshellarg($dumpPath),
        );

        $postgresEnv = $password === '' ? [] : ['PGPASSWORD' => $password];

        $process = Process::fromShellCommandline($command, null, $postgresEnv);
        $process->setTimeout(null);
        $process->mustRun();

        return [
            'path'   => $dumpPath,
            'driver' => 'pgsql',
        ];
    }

    /**
     * @param array<int, string> $mediaPaths
     */
    private function archiveMedia(array $mediaPaths, string $backupPath): string
    {
        if ($mediaPaths === []) {
            $this->components->warn('No media paths configured - skipping media archive generation.');

            $placeholder = $backupPath . '/media.empty';
            File::put($placeholder, '');

            return $placeholder;
        }

        $archivePath = $backupPath . '/media.tar.gz';
        $tarBinary = $this->binary('tar', 'tar');
        $flags = $this->archiveFlags('create_flags', '-czf');
        $command = sprintf('%s %s %s', escapeshellarg($tarBinary), $flags, escapeshellarg($archivePath));

        foreach ($mediaPaths as $path) {
            $command .= sprintf(' -C %s %s', escapeshellarg(dirname($path)), escapeshellarg(basename($path)));
        }

        $process = Process::fromShellCommandline($command);
        $process->setTimeout(null);
        $process->mustRun();

        return $archivePath;
    }

    private function resolveCommitHash(): ?string
    {
        $gitBinary = $this->binary('git', 'git');
        $process = Process::fromShellCommandline(sprintf('%s rev-parse HEAD', escapeshellarg($gitBinary)), base_path());
        $process->run();

        if (! $process->isSuccessful()) {
            return null;
        }

        return trim($process->getOutput()) ?: null;
    }

    private function container(): Container
    {
        /** @var Container $container */
        $container = $this->laravel;

        return $container;
    }

    private function optionString(string $name, string $default): string
    {
        $value = $this->option($name);

        return is_string($value) && $value !== '' ? $value : $default;
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

        throw new RuntimeException(sprintf('Connection value for [%s] must be a scalar or null.', $key));
    }

    private function binary(string $key, string $default): string
    {
        $configured = config("backup.binaries.{$key}");

        if (! is_string($configured) || $configured === '') {
            return $default;
        }

        return $configured;
    }

    private function commandOptions(string $configKey, string $default): string
    {
        $options = config($configKey, $default);

        if (! is_string($options)) {
            return trim($default);
        }

        return trim($options);
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
