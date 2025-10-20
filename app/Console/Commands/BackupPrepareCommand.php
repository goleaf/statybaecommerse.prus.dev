<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Repositories\ProductRepository;
use App\Repositories\UserRepository;
use Carbon\CarbonImmutable;
use Illuminate\Console\Command;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Symfony\Component\Process\Process;
use Throwable;

final class BackupPrepareCommand extends Command
{
    /**
     * @var string
     */
    protected $signature = 'backup:prepare {--connection=} {--media=*}';

    /**
     * @var string
     */
    protected $description = 'Create a timestamped application backup with database dump, media archive, and metadata manifest.';

    public function handle(UserRepository $users, ProductRepository $products): int
    {
        $timestamp = CarbonImmutable::now()->format('Ymd_His');
        $disk = Storage::disk('local');
        $backupRelativePath = "backups/{$timestamp}";
        $disk->makeDirectory($backupRelativePath);
        $backupPath = $disk->path($backupRelativePath);

        $connection = $this->resolveConnection();
        $mediaPaths = $this->resolveMediaPaths();

        $this->info(sprintf('Preparing backup at %s using %s connection.', $backupPath, $connection));

        try {
            $databaseArtifact = $this->dumpDatabase($connection, $backupPath);
            $mediaArtifact = $this->archiveMedia($mediaPaths, $backupPath);
            $commitHash = $this->resolveCommitHash();

            $counts = [
                'users' => $users->count($connection),
                'products' => $products->count($connection),
            ];

            $artifacts = array_filter([
                'database' => $databaseArtifact,
                'media' => $mediaArtifact,
            ]);

            $hashes = [];
            foreach ($artifacts as $key => $artifact) {
                $hashes[$key] = [
                    'file' => $artifact['file'],
                    'sha256' => hash_file('sha256', $backupPath . DIRECTORY_SEPARATOR . $artifact['file']),
                ];
            }

            $metadata = [
                'created_at' => CarbonImmutable::now()->toIso8601String(),
                'timestamp' => $timestamp,
                'commit' => $commitHash,
                'connection' => $connection,
                'counts' => $counts,
                'database' => $databaseArtifact,
                'media' => $mediaArtifact,
                'artifacts' => $hashes,
            ];

            $this->writeMetadata($disk, $backupRelativePath, $metadata);
        } catch (Throwable $exception) {
            Log::error('Failed to prepare backup', [
                'message' => $exception->getMessage(),
                'exception' => $exception,
            ]);

            $this->error('Backup preparation failed: ' . $exception->getMessage());

            return Command::FAILURE;
        }

        $this->info('Backup artifacts have been created successfully.');

        return Command::SUCCESS;
    }

    /**
     * @return array{file:string,driver:string,type:string,database?:string}
     */
    private function dumpDatabase(string $connection, string $backupPath): array
    {
        $config = config("database.connections.{$connection}");
        if ($config === null) {
            throw new RuntimeException("Connection [{$connection}] is not defined.");
        }

        return match ($config['driver'] ?? null) {
            'mysql' => $this->dumpMysqlDatabase($config, $backupPath),
            'pgsql' => $this->dumpPostgresDatabase($config, $backupPath),
            'sqlite' => $this->dumpSqliteDatabase($config, $backupPath),
            default => throw new RuntimeException('Unsupported database driver for backups: ' . ($config['driver'] ?? 'undefined')),
        };
    }

    /**
     * @param array<string,mixed> $config
     * @return array{file:string,driver:string,type:string,database?:string}
     */
    private function dumpMysqlDatabase(array $config, string $backupPath): array
    {
        $command = config('backup.commands.dump.mysql', 'mysqldump');
        $arguments = array_values(array_filter([
            $command,
            isset($config['host']) ? '--host=' . $config['host'] : null,
            isset($config['port']) ? '--port=' . $config['port'] : null,
            isset($config['charset']) ? '--default-character-set=' . $config['charset'] : null,
            '--user=' . ($config['username'] ?? 'root'),
            '--single-transaction',
            '--quick',
            '--lock-tables=false',
            $config['database'] ?? null,
        ]));

        $process = new Process($arguments);
        $process->setTimeout((float) config('backup.process_timeout', 300));

        $environment = [];
        if (!empty($config['password'])) {
            $environment['MYSQL_PWD'] = (string) $config['password'];
        }

        $process->run(null, $environment);

        if (! $process->isSuccessful()) {
            throw new RuntimeException('Database dump failed: ' . $process->getErrorOutput());
        }

        $filename = 'database.sql';
        file_put_contents($backupPath . DIRECTORY_SEPARATOR . $filename, $process->getOutput());

        return [
            'file' => $filename,
            'driver' => 'mysql',
            'type' => 'sql',
            'database' => $config['database'] ?? null,
        ];
    }

    /**
     * @param array<string,mixed> $config
     * @return array{file:string,driver:string,type:string,database?:string}
     */
    private function dumpPostgresDatabase(array $config, string $backupPath): array
    {
        $command = config('backup.commands.dump.pgsql', 'pg_dump');
        $arguments = array_values(array_filter([
            $command,
            isset($config['host']) ? '--host=' . $config['host'] : null,
            isset($config['port']) ? '--port=' . $config['port'] : null,
            '--username=' . ($config['username'] ?? 'postgres'),
            $config['database'] ?? null,
        ]));

        $process = new Process($arguments);
        $process->setTimeout((float) config('backup.process_timeout', 300));

        $environment = [];
        if (!empty($config['password'])) {
            $environment['PGPASSWORD'] = (string) $config['password'];
        }

        $process->run(null, $environment);

        if (! $process->isSuccessful()) {
            throw new RuntimeException('Database dump failed: ' . $process->getErrorOutput());
        }

        $filename = 'database.sql';
        file_put_contents($backupPath . DIRECTORY_SEPARATOR . $filename, $process->getOutput());

        return [
            'file' => $filename,
            'driver' => 'pgsql',
            'type' => 'sql',
            'database' => $config['database'] ?? null,
        ];
    }

    /**
     * @param array<string,mixed> $config
     * @return array{file:string,driver:string,type:string,database?:string}
     */
    private function dumpSqliteDatabase(array $config, string $backupPath): array
    {
        $databasePath = $config['database'] ?? null;
        if (! $databasePath || ! file_exists($databasePath)) {
            throw new RuntimeException('Sqlite database file not found.');
        }

        $filename = 'database.sqlite';
        if (! copy($databasePath, $backupPath . DIRECTORY_SEPARATOR . $filename)) {
            throw new RuntimeException('Failed to copy sqlite database.');
        }

        return [
            'file' => $filename,
            'driver' => 'sqlite',
            'type' => 'sqlite',
            'database' => $databasePath,
        ];
    }

    /**
     * @param list<string> $mediaPaths
     * @return array{file:string,paths:list<string>}|null
     */
    private function archiveMedia(array $mediaPaths, string $backupPath): ?array
    {
        $paths = array_values(array_filter($mediaPaths, static fn ($path) => is_string($path) && $path !== ''));
        if ($paths === []) {
            $this->warn('No media paths configured; skipping media archive.');

            return null;
        }

        $paths = array_map(static fn (string $path) => realpath($path) ?: $path, $paths);
        $filename = 'media.tar.gz';
        $archivePath = $backupPath . DIRECTORY_SEPARATOR . $filename;

        $arguments = ['tar', '-czf', $archivePath];
        foreach ($paths as $resolvedPath) {
            if (! file_exists($resolvedPath)) {
                throw new RuntimeException(sprintf('Media path [%s] does not exist.', $resolvedPath));
            }

            $arguments[] = '-C';
            $arguments[] = dirname($resolvedPath);
            $arguments[] = basename($resolvedPath);
        }
        $process = new Process($arguments);
        $process->setTimeout((float) config('backup.process_timeout', 300));
        $process->run();

        if (! $process->isSuccessful()) {
            throw new RuntimeException('Media archive failed: ' . $process->getErrorOutput());
        }

        return [
            'file' => $filename,
            'paths' => $paths,
        ];
    }

    private function resolveCommitHash(): string
    {
        $process = Process::fromShellCommandline('git rev-parse HEAD');
        $process->run();

        if (! $process->isSuccessful()) {
            $this->warn('Unable to determine git commit hash.');

            return 'unknown';
        }

        return trim($process->getOutput());
    }

    private function resolveConnection(): string
    {
        return $this->option('connection') ?: (string) (config('backup.database_connection') ?? config('database.default'));
    }

    /**
     * @return list<string>
     */
    private function resolveMediaPaths(): array
    {
        $paths = $this->option('media');
        if (is_array($paths) && $paths !== []) {
            return array_values(array_filter($paths, static fn ($path) => is_string($path) && $path !== ''));
        }

        $configured = config('backup.media_paths');
        if ($configured === null) {
            return [storage_path('app/public')];
        }

        if (is_string($configured)) {
            return array_values(array_filter(array_map('trim', explode(',', $configured))));
        }

        return array_values(array_filter(Arr::wrap($configured), static fn ($path) => is_string($path) && $path !== ''));
    }

    /**
     * @param array<string,mixed> $metadata
     */
    private function writeMetadata(FilesystemAdapter $disk, string $relativePath, array $metadata): void
    {
        $disk->put($relativePath . '/metadata.json', json_encode($metadata, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    }
}
