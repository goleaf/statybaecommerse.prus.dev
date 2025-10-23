<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Product;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;

final class BackupPrepareCommand extends Command
{
    protected $signature = 'backup:prepare {--disk=backups : Storage disk to persist artifacts on} {--path=artifacts/backup.json : Relative path on the disk for the backup payload}';

    protected $description = 'Prepare sanitized backup artifacts for critical catalog tables.';

    public function handle(): int
    {
        $diskName = (string) $this->option('disk');
        $path = (string) $this->option('path');

        $disk = Storage::disk($diskName);

        $users = User::query()
            ->select(['id', 'name', 'email', 'locale', 'created_at', 'updated_at'])
            ->orderBy('id')
            ->get()
            ->map(static fn (User $user): array => Arr::only($user->toArray(), ['id', 'name', 'email', 'locale', 'created_at', 'updated_at']))
            ->values();

        $products = Product::query()
            ->select(['id', 'name', 'slug', 'sku', 'created_at', 'updated_at'])
            ->orderBy('id')
            ->get()
            ->map(static fn (Product $product): array => Arr::only($product->toArray(), ['id', 'name', 'slug', 'sku', 'created_at', 'updated_at']))
            ->values();

        $payload = [
            'generated_at' => now()->toIso8601String(),
            'metadata' => [
                'user_count' => $users->count(),
                'product_count' => $products->count(),
            ],
            'users' => $users,
            'products' => $products,
        ];

        $encodedPayload = json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

        if ($encodedPayload === false) {
            $this->components->error('Unable to encode the backup payload.');

            return self::FAILURE;
        }

        $disk->put($path, $encodedPayload);

        $this->components->info(sprintf('Backup prepared on disk [%s] with %d users and %d products.', $diskName, $users->count(), $products->count()));

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
     * @param  array<string, mixed>  $config
     * @return array{path: string, driver: string}
     */
    private function dumpDatabase(string $connection, array $config, string $backupPath): array
    {
        $driver = $this->connectionValue($config, 'driver');

        if ($driver === null || $driver === '') {
            throw new RuntimeException("Database connection [{$connection}] is missing a driver definition.");
        }

        return match ($driver) {
            'sqlite' => $this->dumpSqliteDatabase($config, $backupPath),
            'mysql', 'mariadb' => $this->dumpMysqlDatabase($config, $backupPath),
            'pgsql' => $this->dumpPostgresDatabase($config, $backupPath),
            default => throw new RuntimeException("Dumping for driver [{$driver}] is not supported."),
        };
    }

    /**
     * @param  array<string, mixed>  $config
     * @return array{path: string, driver: string}
     */
    private function dumpSqliteDatabase(array $config, string $backupPath): array
    {
        $databasePath = $config['database'] ?? null;

        if (! is_string($databasePath) || $databasePath === '') {
            throw new RuntimeException('SQLite database path is not configured.');
        }

        if (! File::exists($databasePath)) {
            throw new FileNotFoundException("SQLite database [{$databasePath}] not found.");
        }

        $targetPath = $backupPath.'/database.sqlite';
        File::copy($databasePath, $targetPath);

        return [
            'path' => $targetPath,
            'driver' => 'sqlite',
        ];
    }

    /**
     * @param  array<string, mixed>  $config
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

        $dumpPath = $backupPath.'/database.sql';
        $binary = $this->binary('mysqldump', 'mysqldump');
        $options = $this->commandOptions('backup.dump.mysql.options', '--single-transaction --routines --events');
        $optionsPart = $options === '' ? '' : ' '.$options;

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
            'path' => $dumpPath,
            'driver' => 'mysql',
        ];
    }

    /**
     * @param  array<string, mixed>  $config
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

        $dumpPath = $backupPath.'/database.sql';
        $binary = $this->binary('pg_dump', 'pg_dump');
        $options = $this->commandOptions('backup.dump.pgsql.options', '--no-owner --no-privileges');
        $optionsPart = $options === '' ? '' : ' '.$options;

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
            'path' => $dumpPath,
            'driver' => 'pgsql',
        ];
    }

    /**
     * @param  array<int, string>  $mediaPaths
     */
    private function archiveMedia(array $mediaPaths, string $backupPath): string
    {
        if ($mediaPaths === []) {
            $this->components->warn('No media paths configured - skipping media archive generation.');

            $placeholder = $backupPath.'/media.empty';
            File::put($placeholder, '');

            return $placeholder;
        }

        $archivePath = $backupPath.'/media.tar.gz';
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

    private function optionString(string $name, string $default): string
    {
        $value = $this->option($name);

        return is_string($value) && $value !== '' ? $value : $default;
    }

    /**
     * @param  array<string, mixed>  $config
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
