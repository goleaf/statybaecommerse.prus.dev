<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Support\Repositories\ProductRepository;
use App\Support\Repositories\UserRepository;
use Carbon\CarbonImmutable;
use Illuminate\Console\Command;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\DB;
use RuntimeException;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

final class BackupPrepareCommand extends Command
{
    protected $signature = 'backup:prepare';

    protected $description = 'Dump the application database, archive media assets, and capture metadata for disaster recovery.';

    public function handle(): int
    {
        $filesystem = new Filesystem;
        $timestamp = CarbonImmutable::now()->format('Ymd_His');
        $storageRoot = $this->stringOrDefault(config('backup.storage_root'), storage_path('app/backups'));
        $backupPath = $storageRoot.DIRECTORY_SEPARATOR.$timestamp;
        $filesystem->ensureDirectoryExists($backupPath);

        $defaultConnection = config('database.default');
        $fallbackConnection = is_string($defaultConnection) && $defaultConnection !== '' ? $defaultConnection : 'mysql';
        $connectionName = $this->stringOrDefault(config('backup.database.connection'), $fallbackConnection);
        /** @var array<string, mixed>|null $connectionConfig */
        $connectionConfig = config("database.connections.{$connectionName}");
        if (! is_array($connectionConfig)) {
            $this->error(sprintf('Database connection [%s] is not configured.', $connectionName));

            return self::FAILURE;
        }

        $this->info(sprintf('Creating backup at [%s].', $backupPath));

        $dumpResult = $this->dumpDatabase($connectionName, $connectionConfig, $backupPath);
        if ($dumpResult === null) {
            return self::FAILURE;
        }

        [$dumpPath, $driver] = $dumpResult;
        $this->info(sprintf('Database dump created: %s', $dumpPath));

        $mediaArchive = $this->archiveMedia($backupPath);
        if ($mediaArchive !== null) {
            $this->info(sprintf('Media archive created: %s', $mediaArchive));
        } else {
            $this->warn('No media directories configured for backup. Skipping media archive.');
        }

        $commitHash = $this->resolveCommitHash();
        $this->info(sprintf('Captured commit hash: %s', $commitHash ?? 'unknown'));

        $connection = DB::connection($connectionName);
        $sanity = $this->captureSanityMetrics($connection);

        try {
            $manifest = $this->writeManifest(
                $backupPath,
                $dumpPath,
                $driver,
                $connectionName,
                $connectionConfig['database'] ?? null,
                $commitHash,
                $mediaArchive,
                $sanity,
            );
        } catch (\JsonException $exception) {
            $this->error('Failed to write manifest: '.$exception->getMessage());

            return self::FAILURE;
        }

        $this->info(sprintf('Backup manifest stored: %s', $manifest));

        return self::SUCCESS;
    }

    /**
     * @param  array<string, mixed>  $connectionConfig
     * @return array{string, string}|null
     */
    private function dumpDatabase(string $connectionName, array $connectionConfig, string $backupPath): ?array
    {
        $driver = $connectionConfig['driver'] ?? null;
        if (! is_string($driver)) {
            $this->error(sprintf('Database connection [%s] does not define a driver.', $connectionName));

            return null;
        }

        return match ($driver) {
            'mysql' => [$this->dumpMysql($connectionConfig, $backupPath), $driver],
            'pgsql' => [$this->dumpPgsql($connectionConfig, $backupPath), $driver],
            'sqlite' => [$this->dumpSqlite($connectionConfig, $backupPath), $driver],
            default => $this->handleUnsupportedDriver($driver),
        };
    }

    /**
     * @param  array<string, mixed>  $connectionConfig
     */
    private function dumpMysql(array $connectionConfig, string $backupPath): string
    {
        $database = $this->requireConfigValue($connectionConfig, 'database');
        $host = $this->stringOrDefault($connectionConfig['host'] ?? null, '127.0.0.1');
        $port = $this->stringOrDefault($connectionConfig['port'] ?? null, '3306');
        $username = $this->stringOrDefault($connectionConfig['username'] ?? null, 'root');
        $password = $this->stringOrDefault($connectionConfig['password'] ?? null, '');
        $dumpBinary = $this->stringOrDefault(config('backup.database.dump_binary'), 'mysqldump');
        $dumpPath = $backupPath.DIRECTORY_SEPARATOR.'database.sql';

        $command = sprintf(
            '%s --host=%s --port=%s --user=%s --single-transaction --routines --events --triggers --result-file=%s %s',
            escapeshellcmd($dumpBinary),
            escapeshellarg($host),
            escapeshellarg($port),
            escapeshellarg($username),
            escapeshellarg($dumpPath),
            escapeshellarg($database),
        );

        $process = Process::fromShellCommandline($command, base_path(), $password === '' ? null : ['MYSQL_PWD' => $password]);
        $process->setTimeout(null);
        $process->run();

        if (! $process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        return $dumpPath;
    }

    /**
     * @param  array<string, mixed>  $connectionConfig
     */
    private function dumpPgsql(array $connectionConfig, string $backupPath): string
    {
        $database = $this->requireConfigValue($connectionConfig, 'database');
        $host = $this->stringOrDefault($connectionConfig['host'] ?? null, '127.0.0.1');
        $port = $this->stringOrDefault($connectionConfig['port'] ?? null, '5432');
        $username = $this->stringOrDefault($connectionConfig['username'] ?? null, 'postgres');
        $password = $this->stringOrDefault($connectionConfig['password'] ?? null, '');
        $dumpBinary = $this->stringOrDefault(config('backup.database.dump_binary'), 'pg_dump');
        $dumpPath = $backupPath.DIRECTORY_SEPARATOR.'database.sql';

        $command = sprintf(
            '%s --host=%s --port=%s --username=%s --format=plain --no-owner --file=%s %s',
            escapeshellcmd($dumpBinary),
            escapeshellarg($host),
            escapeshellarg($port),
            escapeshellarg($username),
            escapeshellarg($dumpPath),
            escapeshellarg($database),
        );

        $process = Process::fromShellCommandline($command, base_path(), $password === '' ? null : ['PGPASSWORD' => $password]);
        $process->setTimeout(null);
        $process->run();

        if (! $process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        return $dumpPath;
    }

    /**
     * @param  array<string, mixed>  $connectionConfig
     */
    private function dumpSqlite(array $connectionConfig, string $backupPath): string
    {
        $database = $this->requireConfigValue($connectionConfig, 'database');
        if ($database === ':memory:') {
            throw new RuntimeException('Cannot dump an in-memory SQLite database.');
        }

        $source = realpath($database) ?: $database;
        $dumpPath = $backupPath.DIRECTORY_SEPARATOR.'database.sqlite';

        $filesystem = new Filesystem;
        $filesystem->copy($source, $dumpPath);

        return $dumpPath;
    }

    /**
     * @return array{string, string}|null
     */
    private function handleUnsupportedDriver(string $driver): ?array
    {
        $this->error(sprintf('Unsupported database driver [%s] for backups.', $driver));

        return null;
    }

    /**
     * @param  array<string, mixed>  $connectionConfig
     */
    private function requireConfigValue(array $connectionConfig, string $key): string
    {
        $value = $connectionConfig[$key] ?? null;
        if (! is_string($value) || $value === '') {
            throw new RuntimeException(sprintf('Database configuration key [%s] is required for backups.', $key));
        }

        return $value;
    }

    private function archiveMedia(string $backupPath): ?string
    {
        $paths = array_values(array_filter(
            $this->mediaSources(),
            static fn (string $path): bool => file_exists($path),
        ));

        if ($paths === []) {
            return null;
        }

        $archivePath = $backupPath.DIRECTORY_SEPARATOR.'media.tar.gz';
        $command = array_merge(['tar', '-czf', $archivePath], $paths);
        $process = new Process($command, base_path());
        $process->setTimeout(null);
        $process->run();

        if (! $process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        return $archivePath;
    }

    private function resolveCommitHash(): ?string
    {
        $process = new Process(['git', 'rev-parse', 'HEAD'], base_path());
        $process->run();

        if (! $process->isSuccessful()) {
            return null;
        }

        return trim($process->getOutput());
    }

    /**
     * @return array<string, int>
     */
    private function captureSanityMetrics(ConnectionInterface $connection): array
    {
        $userRepository = new UserRepository($connection);
        $productRepository = new ProductRepository($connection);

        return [
            'users' => $userRepository->count(),
            'products' => $productRepository->count(),
        ];
    }

    /**
     * @param  array<string, int>  $sanity
     */
    private function writeManifest(
        string $backupPath,
        string $dumpPath,
        string $driver,
        string $connectionName,
        ?string $databaseName,
        ?string $commitHash,
        ?string $mediaArchive,
        array $sanity,
    ): string {
        $manifestPath = $backupPath.DIRECTORY_SEPARATOR.'manifest.json';
        $manifest = [
            'timestamp' => CarbonImmutable::now()->toIso8601String(),
            'commit_hash' => $commitHash,
            'database' => [
                'connection' => $connectionName,
                'driver' => $driver,
                'database' => $databaseName,
                'dump' => basename($dumpPath),
                'hash' => hash_file('sha256', $dumpPath),
            ],
            'media' => $mediaArchive !== null ? [
                'archive' => basename($mediaArchive),
                'hash' => hash_file('sha256', $mediaArchive),
                'sources' => $this->mediaSources(),
            ] : null,
            'sanity' => $sanity,
        ];

        $json = json_encode($manifest, JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        file_put_contents($manifestPath, $json);

        return $manifestPath;
    }

    /**
     * @return array<int, string>
     */
    private function mediaSources(): array
    {
        $configuredPaths = config('backup.media_paths');
        if (! is_array($configuredPaths)) {
            $configuredPaths = [storage_path('app/public')];
        }

        return array_values(array_filter(array_map(
            static fn ($path): string => is_string($path) ? trim($path) : '',
            $configuredPaths,
        ), static fn (string $path): bool => $path !== ''));
    }

    private function stringOrDefault(mixed $value, string $default): string
    {
        return is_string($value) && $value !== '' ? $value : $default;
    }
}
