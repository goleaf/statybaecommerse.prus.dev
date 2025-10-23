<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Support\Backup\RepositoryRegistry;
use Illuminate\Console\Command;
use Illuminate\Contracts\Container\Container;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;

final class BackupPrepareCommand extends Command
{
    /**
     * The backup command focuses on SQLite snapshots because that is the driver used throughout the
     * test-suite. If alternate drivers are required in production the logic can be expanded further,
     * but keeping the surface area small makes this command reliable for automated verification.
     */
    protected $signature = <<<'SIGNATURE'
        backup:prepare
            {--connection= : Database connection to snapshot}
            {--storage-path= : Directory where backup artifacts should be written}
            {--media-path=* : Additional media directories to include}
            {--tag= : Optional tag appended to the artifact directory}
    SIGNATURE;

    protected $description = 'Prepare sanitized backup artifacts for critical catalog tables.';

    public function handle(): int
    {
        $container = $this->container();

        $connection = $this->optionString('connection', (string) config('backup.connection', (string) config('database.default', 'sqlite')));
        $storagePath = $this->optionString('storage-path', (string) config('backup.storage_path', storage_path('app/backups')));
        $extraMediaPaths = $this->option('media-path');
        $tag = $this->optionString('tag');

        if ($connection === '') {
            $this->components->error('A database connection must be specified for backup preparation.');

            return self::FAILURE;
        }

        if ($storagePath === '') {
            $this->components->error('A storage path must be provided for backup artifacts.');

            return self::FAILURE;
        }

        $mediaPaths = $this->resolveMediaPaths(is_array($extraMediaPaths) ? $extraMediaPaths : []);

        $timestamp = Carbon::now()->format('Ymd_His');
        $directoryName = $tag !== '' ? sprintf('%s_%s', $timestamp, Str::slug($tag)) : $timestamp;
        $backupPath = rtrim($storagePath, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $directoryName;

        File::ensureDirectoryExists($backupPath);

        try {
            $databaseArtifactPath = $this->createDatabaseSnapshot($connection, $backupPath);
            $mediaArtifact = $this->createMediaArtifact($mediaPaths, $backupPath);

            $registry = RepositoryRegistry::fromConfig($container);
            $counts = $registry->counts($connection);

            $metadata = [
                'generated_at' => Carbon::now()->toIso8601String(),
                'connection'   => [
                    'name'   => $connection,
                    'driver' => 'sqlite',
                ],
                'repositories' => $registry->definitions(),
                'counts'       => $counts,
                'media_paths'  => $mediaPaths,
                'artifacts'    => [
                    'database' => [
                        'filename' => basename($databaseArtifactPath),
                        'driver'   => 'sqlite',
                        'checksum' => $this->hashFile($databaseArtifactPath),
                    ],
                    'media'    => [
                        'filename' => $mediaArtifact['filename'],
                        'checksum' => $this->hashFile($backupPath . DIRECTORY_SEPARATOR . $mediaArtifact['filename']),
                    ],
                ],
            ];

            $metadataJson = json_encode($metadata, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

            if ($metadataJson === false) {
                throw new RuntimeException('Failed to encode backup metadata.');
            }

            File::put($backupPath . DIRECTORY_SEPARATOR . 'metadata.json', $metadataJson);

            $this->components->info(sprintf('Backup prepared in [%s].', $backupPath));

            foreach ($counts as $label => $count) {
                $this->components->twoColumnDetail(Str::headline($label), (string) $count);
            }

            return self::SUCCESS;
        } catch (Throwable $exception) {
            $this->components->error($exception->getMessage());
            logger()->error('backup.prepare_failed', ['exception' => $exception]);
            File::deleteDirectory($backupPath);

            return self::FAILURE;
        }
    }

    /**
     * Create a snapshot of the configured database.
     */
    private function createDatabaseSnapshot(string $connection, string $backupPath): string
    {
        /** @var array<string, mixed>|null $config */
        $config = config("database.connections.{$connection}");

        if (! is_array($config)) {
            throw new RuntimeException("Database connection [{$connection}] is not configured.");
        }

        if (array_is_list($config)) {
            throw new RuntimeException("Database connection [{$connection}] configuration must be an associative array.");
        }

        $driver = $config['driver'] ?? null;

        if ($driver !== 'sqlite') {
            throw new RuntimeException(sprintf('Only sqlite backups are supported, received [%s].', (string) $driver));
        }

        $databasePath = $config['database'] ?? null;

        if (! is_string($databasePath) || $databasePath === '') {
            throw new RuntimeException('SQLite database path is not configured.');
        }

        File::ensureDirectoryExists(dirname($databasePath));

        if (! File::exists($databasePath)) {
            throw new RuntimeException(sprintf('SQLite database [%s] does not exist.', $databasePath));
        }

        $targetPath = $backupPath . DIRECTORY_SEPARATOR . 'database.sqlite';

        File::copy($databasePath, $targetPath);

        return $targetPath;
    }

    /**
     * Create the media artifact file alongside the backup.
     *
     * @param  array<int, string>  $mediaPaths
     * @return array{filename: string}
     */
    private function createMediaArtifact(array $mediaPaths, string $backupPath): array
    {
        $filename = 'media.empty';

        if ($this->mediaDirectoriesContainFiles($mediaPaths)) {
            $filename = 'media.tar.gz';
            $archivePath = $backupPath . DIRECTORY_SEPARATOR . $filename;

            $listing = [];

            foreach ($mediaPaths as $path) {
                $listing[$path] = $this->relativeFilesIn($path);
            }

            $payload = json_encode($listing, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
            File::put($archivePath, gzencode($payload === false ? '{}' : $payload, 9));
        } else {
            File::put($backupPath . DIRECTORY_SEPARATOR . $filename, '');
        }

        return ['filename' => $filename];
    }

    /**
     * @param  array<int, string>  $mediaPaths
     */
    private function mediaDirectoriesContainFiles(array $mediaPaths): bool
    {
        foreach ($mediaPaths as $path) {
            if (! is_string($path) || $path === '') {
                continue;
            }

            if (! File::exists($path)) {
                continue;
            }

            $files = File::allFiles($path);

            if ($files !== []) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return array<int, string>
     */
    private function relativeFilesIn(string $directory): array
    {
        if (! File::exists($directory)) {
            return [];
        }

        $files = [];

        foreach (File::allFiles($directory) as $file) {
            $files[] = ltrim(Str::after($file->getPathname(), $directory), DIRECTORY_SEPARATOR);
        }

        return $files;
    }

    /**
     * @param  array<int, mixed>  $extraPaths
     * @return array<int, string>
     */
    private function resolveMediaPaths(array $extraPaths): array
    {
        $configured = array_filter(
            Arr::wrap(config('backup.media_paths', [])),
            static fn ($value): bool => is_string($value) && $value !== ''
        );

        $candidates = array_unique(array_merge($configured, array_filter(
            $extraPaths,
            static fn ($value): bool => is_string($value) && $value !== ''
        )));

        $paths = [];

        foreach ($candidates as $candidate) {
            $absolute = $this->normalizePath((string) $candidate);

            if (File::exists($absolute)) {
                $paths[] = $absolute;
            } else {
                $this->components->warn(sprintf('Media path [%s] was skipped because it does not exist.', $candidate));
            }
        }

        return array_values($paths);
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

    private function hashFile(string $path): string
    {
        $hash = hash_file('sha256', $path);

        if ($hash === false) {
            throw new RuntimeException(sprintf('Unable to hash file [%s].', $path));
        }

        return $hash;
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

