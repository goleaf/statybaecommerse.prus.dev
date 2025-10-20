<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Repositories\ProductRepository;
use App\Repositories\UserRepository;
use Illuminate\Console\Command;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;
use Symfony\Component\Process\Process;
use Throwable;

final class BackupVerifyCommand extends Command
{
    /**
     * @var string
     */
    protected $signature = 'backup:verify {--connection=} {--database=} {--keep-temp}';

    /**
     * @var string
     */
    protected $description = 'Verify the latest backup by restoring into an ephemeral database and validating artifact integrity.';

    public function handle(UserRepository $users, ProductRepository $products): int
    {
        $disk = Storage::disk('local');
        $latestRelativePath = $this->resolveLatestBackup($disk);
        if ($latestRelativePath === null) {
            $this->error('No backups found to verify.');

            return Command::FAILURE;
        }

        $backupPath = $disk->path($latestRelativePath);
        try {
            $metadata = $this->readMetadata($disk, $latestRelativePath);
        } catch (Throwable $exception) {
            $this->error('Unable to read backup metadata: ' . $exception->getMessage());

            return Command::FAILURE;
        }

        $this->info(sprintf('Verifying backup at %s', $backupPath));

        try {
            $this->verifyArtifacts($backupPath, $metadata['artifacts'] ?? []);
        } catch (Throwable $exception) {
            $this->error('Artifact verification failed: ' . $exception->getMessage());

            return Command::FAILURE;
        }

        $tempRelativePath = 'backup-temp/' . Str::uuid()->toString();
        $disk->makeDirectory($tempRelativePath);
        $tempPath = $disk->path($tempRelativePath);

        $cleanup = static function (): void {
        };

        try {
            $this->extractMedia($backupPath, $metadata['media'] ?? null, $tempPath);

            $connectionName = $this->option('connection') ?: (string) (config('backup.verify.connection') ?? config('backup.database_connection') ?? config('database.default'));
            $databaseName = $this->option('database') ?: config('backup.verify.database');

            $restoreContext = $this->restoreDatabase(
                $metadata['database'] ?? null,
                $backupPath,
                $connectionName,
                $databaseName,
                $tempPath,
            );

            $cleanup = $restoreContext['cleanup'];
            $verifiedConnection = $restoreContext['connection'];

            $this->runSanityChecks($verifiedConnection, $metadata['counts'] ?? [], $users, $products);
        } catch (Throwable $exception) {
            Log::error('Backup verification failed', [
                'message' => $exception->getMessage(),
                'exception' => $exception,
            ]);

            $this->error('Backup verification failed: ' . $exception->getMessage());

            $cleanup();
            if (! $this->option('keep-temp')) {
                $disk->deleteDirectory($tempRelativePath);
            } else {
                $this->warn(sprintf('Temporary verification data retained at %s for inspection.', $tempPath));
            }

            return Command::FAILURE;
        }

        $cleanup();
        if (! $this->option('keep-temp')) {
            $disk->deleteDirectory($tempRelativePath);
        } else {
            $this->warn(sprintf('Temporary verification data retained at %s', $tempPath));
        }

        $this->info('Backup verification completed successfully.');

        return Command::SUCCESS;
    }

    /**
     * @param array<string, array{file:string,sha256:string}> $artifacts
     */
    private function verifyArtifacts(string $backupPath, array $artifacts): void
    {
        foreach ($artifacts as $key => $artifact) {
            $file = $artifact['file'] ?? null;
            $hash = $artifact['sha256'] ?? null;
            if (! $file || ! $hash) {
                throw new RuntimeException("Artifact metadata for {$key} is incomplete.");
            }

            $path = $backupPath . DIRECTORY_SEPARATOR . $file;
            if (! file_exists($path)) {
                throw new RuntimeException("Artifact {$file} is missing.");
            }

            $currentHash = hash_file('sha256', $path);
            if (! hash_equals($hash, $currentHash)) {
                throw new RuntimeException("Artifact {$file} failed checksum validation.");
            }
        }
    }

    /**
     * @param array{file?:string,paths?:array<int,string>}|null $media
     */
    private function extractMedia(string $backupPath, ?array $media, string $tempPath): void
    {
        if ($media === null || empty($media['file'])) {
            $this->warn('Backup did not include media archive.');

            return;
        }

        $archive = $backupPath . DIRECTORY_SEPARATOR . $media['file'];
        if (! file_exists($archive)) {
            throw new RuntimeException('Media archive missing from backup.');
        }

        $target = $tempPath . DIRECTORY_SEPARATOR . 'media';
        if (! is_dir($target) && ! mkdir($target, 0777, true) && ! is_dir($target)) {
            throw new RuntimeException('Unable to create directory for media extraction.');
        }

        $process = new Process(['tar', '-xzf', $archive, '-C', $target]);
        $process->setTimeout((float) config('backup.process_timeout', 300));
        $process->run();

        if (! $process->isSuccessful()) {
            throw new RuntimeException('Failed to extract media archive: ' . $process->getErrorOutput());
        }
    }

    /**
     * @param array{file?:string,driver?:string,type?:string,database?:string}|null $database
     * @return array{connection:string,cleanup:callable}
     */
    private function restoreDatabase(?array $database, string $backupPath, string $connectionName, ?string $databaseOverride, string $tempPath): array
    {
        if ($database === null || empty($database['file'])) {
            throw new RuntimeException('Database metadata is missing from the backup.');
        }

        $dumpPath = $backupPath . DIRECTORY_SEPARATOR . $database['file'];
        if (! file_exists($dumpPath)) {
            throw new RuntimeException('Database dump is missing from the backup.');
        }

        $connectionConfig = config("database.connections.{$connectionName}");
        if ($connectionConfig === null) {
            throw new RuntimeException("Verification connection [{$connectionName}] is not configured.");
        }

        $driver = $database['driver'] ?? $connectionConfig['driver'] ?? null;
        if ($driver === null) {
            throw new RuntimeException('Unable to determine database driver for verification.');
        }

        return match ($driver) {
            'mysql' => $this->restoreMysqlDatabase($connectionName, $connectionConfig, $dumpPath, $databaseOverride ?? $database['database'] ?? null),
            'pgsql' => $this->restorePostgresDatabase($connectionName, $connectionConfig, $dumpPath, $databaseOverride ?? $database['database'] ?? null),
            'sqlite' => $this->restoreSqliteDatabase($connectionName, $connectionConfig, $dumpPath, $databaseOverride, $tempPath),
            default => throw new RuntimeException('Unsupported database driver for verification: ' . $driver),
        };
    }

    /**
     * @param array<string,mixed> $config
     * @return array{connection:string,cleanup:callable}
     */
    private function restoreMysqlDatabase(string $connectionName, array $config, string $dumpPath, ?string $databaseName): array
    {
        $command = config('backup.commands.restore.mysql', 'mysql');
        $database = $databaseName ?: (($config['database'] ?? 'laravel') . '_verify');

        $base = array_values(array_filter([
            $command,
            isset($config['host']) ? '--host=' . $config['host'] : null,
            isset($config['port']) ? '--port=' . $config['port'] : null,
            '--user=' . ($config['username'] ?? 'root'),
        ]));

        $password = $config['password'] ?? null;
        $env = [];
        if (! empty($password)) {
            $env['MYSQL_PWD'] = (string) $password;
        }

        $this->runMysqlStatement($base, $env, sprintf('DROP DATABASE IF EXISTS `%s`;', $database));
        $this->runMysqlStatement($base, $env, sprintf('CREATE DATABASE `%s`;', $database));

        $sql = file_get_contents($dumpPath);
        if ($sql === false) {
            throw new RuntimeException('Unable to read database dump for import.');
        }

        $process = new Process(array_merge($base, [$database]));
        $process->setTimeout((float) config('backup.process_timeout', 300));
        $process->setInput($sql);
        $process->run(null, $env);

        if (! $process->isSuccessful()) {
            throw new RuntimeException('Database import failed: ' . $process->getErrorOutput());
        }

        $originalConfig = $config;
        config(["database.connections.{$connectionName}" => array_merge($config, ['database' => $database])]);
        DB::purge($connectionName);

        return [
            'connection' => $connectionName,
            'cleanup' => function () use ($connectionName, $originalConfig, $base, $env, $database): void {
                config(["database.connections.{$connectionName}" => $originalConfig]);
                DB::purge($connectionName);
                $this->runMysqlStatement($base, $env, sprintf('DROP DATABASE IF EXISTS `%s`;', $database));
            },
        ];
    }

    /**
     * @param list<string> $base
     * @param array<string,string> $env
     */
    private function runMysqlStatement(array $base, array $env, string $statement): void
    {
        $process = new Process(array_merge($base, ['--execute=' . $statement]));
        $process->setTimeout((float) config('backup.process_timeout', 120));
        $process->run(null, $env);

        if (! $process->isSuccessful()) {
            throw new RuntimeException('MySQL statement failed: ' . $process->getErrorOutput());
        }
    }

    /**
     * @param array<string,mixed> $config
     * @return array{connection:string,cleanup:callable}
     */
    private function restorePostgresDatabase(string $connectionName, array $config, string $dumpPath, ?string $databaseName): array
    {
        $command = config('backup.commands.restore.pgsql', 'psql');
        $database = $databaseName ?: (($config['database'] ?? 'laravel') . '_verify');
        $maintenanceDatabase = config('backup.verify.maintenance_database', 'postgres');

        $base = array_values(array_filter([
            $command,
            isset($config['host']) ? '--host=' . $config['host'] : null,
            isset($config['port']) ? '--port=' . $config['port'] : null,
            '--username=' . ($config['username'] ?? 'postgres'),
        ]));

        $env = [];
        if (! empty($config['password'])) {
            $env['PGPASSWORD'] = (string) $config['password'];
        }

        $this->runPostgresStatement($base, $env, $maintenanceDatabase, sprintf('DROP DATABASE IF EXISTS "%s";', $database));
        $this->runPostgresStatement($base, $env, $maintenanceDatabase, sprintf('CREATE DATABASE "%s";', $database));

        $process = new Process(array_merge($base, ['--dbname=' . $database, '--file=' . $dumpPath]));
        $process->setTimeout((float) config('backup.process_timeout', 300));
        $process->run(null, $env);

        if (! $process->isSuccessful()) {
            throw new RuntimeException('PostgreSQL import failed: ' . $process->getErrorOutput());
        }

        $originalConfig = $config;
        config(["database.connections.{$connectionName}" => array_merge($config, ['database' => $database])]);
        DB::purge($connectionName);

        return [
            'connection' => $connectionName,
            'cleanup' => function () use ($connectionName, $originalConfig, $base, $env, $maintenanceDatabase, $database): void {
                config(["database.connections.{$connectionName}" => $originalConfig]);
                DB::purge($connectionName);
                $this->runPostgresStatement($base, $env, $maintenanceDatabase, sprintf('DROP DATABASE IF EXISTS "%s";', $database));
            },
        ];
    }

    /**
     * @param list<string> $base
     * @param array<string,string> $env
     */
    private function runPostgresStatement(array $base, array $env, string $database, string $statement): void
    {
        $process = new Process(array_merge($base, ['--dbname=' . $database, '--command=' . $statement]));
        $process->setTimeout((float) config('backup.process_timeout', 120));
        $process->run(null, $env);

        if (! $process->isSuccessful()) {
            throw new RuntimeException('PostgreSQL statement failed: ' . $process->getErrorOutput());
        }
    }

    /**
     * @param array<string,mixed> $config
     * @return array{connection:string,cleanup:callable}
     */
    private function restoreSqliteDatabase(string $connectionName, array $config, string $dumpPath, ?string $databaseOverride, string $tempPath): array
    {
        $target = $tempPath . DIRECTORY_SEPARATOR . ($databaseOverride ?: 'database.sqlite');
        if (! copy($dumpPath, $target)) {
            throw new RuntimeException('Failed to copy sqlite database for verification.');
        }

        $originalConfig = $config;
        config(["database.connections.{$connectionName}" => array_merge($config, ['database' => $target])]);
        DB::purge($connectionName);

        return [
            'connection' => $connectionName,
            'cleanup' => function () use ($connectionName, $originalConfig, $target): void {
                config(["database.connections.{$connectionName}" => $originalConfig]);
                DB::purge($connectionName);
                if (file_exists($target)) {
                    @unlink($target);
                }
            },
        ];
    }

    private function runSanityChecks(string $connection, array $expectedCounts, UserRepository $users, ProductRepository $products): void
    {
        $userCount = $users->count($connection);
        $productCount = $products->count($connection);

        $mismatches = [];
        if (isset($expectedCounts['users']) && (int) $expectedCounts['users'] !== $userCount) {
            $mismatches[] = sprintf('users (expected %d, got %d)', (int) $expectedCounts['users'], $userCount);
        }
        if (isset($expectedCounts['products']) && (int) $expectedCounts['products'] !== $productCount) {
            $mismatches[] = sprintf('products (expected %d, got %d)', (int) $expectedCounts['products'], $productCount);
        }

        if ($mismatches !== []) {
            throw new RuntimeException('Sanity checks failed for: ' . implode(', ', $mismatches));
        }

        $this->info(sprintf('Sanity checks passed (users: %d, products: %d).', $userCount, $productCount));
    }

    /**
     * @return array<string,mixed>
     */
    private function readMetadata(FilesystemAdapter $disk, string $relativePath): array
    {
        if (! $disk->exists($relativePath . '/metadata.json')) {
            throw new RuntimeException('Metadata file missing from backup.');
        }

        $metadata = json_decode($disk->get($relativePath . '/metadata.json'), true, 512, JSON_THROW_ON_ERROR);

        return is_array($metadata) ? $metadata : [];
    }

    private function resolveLatestBackup(FilesystemAdapter $disk): ?string
    {
        $directories = $disk->directories('backups');
        if ($directories === []) {
            return null;
        }

        usort($directories, static fn (string $a, string $b) => strcmp($b, $a));

        return Arr::first($directories);
    }
}
