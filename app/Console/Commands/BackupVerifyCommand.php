<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Support\Repositories\ProductRepository;
use App\Support\Repositories\UserRepository;
use Illuminate\Console\Command;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

final class BackupVerifyCommand extends Command
{
    protected $signature = 'backup:verify {--keep-temp : Retain the extracted artifacts for inspection.}';

    protected $description = 'Verify the newest application backup by restoring it into an ephemeral database and validating artifacts.';

    public function handle(): int
    {
        $filesystem = new Filesystem;
        $storageRoot = $this->stringOrDefault(config('backup.storage_root'), storage_path('app/backups'));
        if (! $filesystem->exists($storageRoot)) {
            $this->error(sprintf('Backup root directory [%s] does not exist.', $storageRoot));

            return self::FAILURE;
        }

        $directories = collect($filesystem->directories($storageRoot))->sortDesc()->values();
        if ($directories->isEmpty()) {
            $this->error('No backups were found to verify.');

            return self::FAILURE;
        }

        $backupPath = $directories->first();
        $this->info(sprintf('Verifying backup at [%s].', $backupPath));

        $manifestPath = $backupPath.DIRECTORY_SEPARATOR.'manifest.json';
        if (! $filesystem->exists($manifestPath)) {
            $this->error('Backup manifest is missing.');

            return self::FAILURE;
        }

        try {
            $manifest = json_decode($filesystem->get($manifestPath), true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException $exception) {
            $this->error('Failed to read backup manifest: '.$exception->getMessage());

            return self::FAILURE;
        }

        if (! is_array($manifest)) {
            $this->error('Backup manifest is malformed.');

            return self::FAILURE;
        }

        $dumpRelative = Arr::get($manifest, 'database.dump');
        if (! is_string($dumpRelative) || $dumpRelative === '') {
            $this->error('Database dump reference is missing from the manifest.');

            return self::FAILURE;
        }

        $dumpFile = $backupPath.DIRECTORY_SEPARATOR.$dumpRelative;
        if (! $filesystem->exists($dumpFile)) {
            $this->error('Database dump file is missing.');

            return self::FAILURE;
        }

        $expectedDumpHash = Arr::get($manifest, 'database.hash');
        if (is_string($expectedDumpHash) && $expectedDumpHash !== '' && hash_file('sha256', $dumpFile) !== $expectedDumpHash) {
            $this->error('Database dump hash does not match the manifest.');

            return self::FAILURE;
        }

        $mediaArchive = null;
        $mediaConfig = Arr::get($manifest, 'media');
        if (is_array($mediaConfig)) {
            $archiveName = $mediaConfig['archive'] ?? null;
            if (is_string($archiveName) && $archiveName !== '') {
                $candidate = $backupPath.DIRECTORY_SEPARATOR.$archiveName;
                if ($filesystem->exists($candidate)) {
                    $expectedMediaHash = $mediaConfig['hash'] ?? null;
                    if (is_string($expectedMediaHash) && $expectedMediaHash !== '' && hash_file('sha256', $candidate) !== $expectedMediaHash) {
                        $this->error('Media archive hash does not match the manifest.');

                        return self::FAILURE;
                    }

                    $mediaArchive = $candidate;
                } else {
                    $this->warn('Media archive listed in the manifest is missing.');
                }
            }
        }

        $tempRoot = $this->stringOrDefault(config('backup.verify.temp_root'), storage_path('app/backup-verification'));
        $filesystem->ensureDirectoryExists($tempRoot);
        $tempPath = $tempRoot.DIRECTORY_SEPARATOR.Str::uuid()->toString();
        $filesystem->ensureDirectoryExists($tempPath);

        try {
            if ($mediaArchive !== null) {
                $this->extractMediaArchive($mediaArchive, $tempPath);
                $this->info('Media archive extracted successfully.');
            }

            $databaseConfig = Arr::get($manifest, 'database');
            if (! is_array($databaseConfig)) {
                throw new RuntimeException('Database configuration is missing from the manifest.');
            }

            /** @var array<string, mixed> $databaseConfig */
            $databaseConfig = $databaseConfig;

            $connection = $this->restoreDatabase($databaseConfig, $dumpFile);
            $sanityConfig = Arr::get($manifest, 'sanity', []);
            if (! is_array($sanityConfig)) {
                $sanityConfig = [];
            }

            $this->runSanityChecks($connection, $sanityConfig);
        } catch (RuntimeException|ProcessFailedException $exception) {
            $this->error($exception->getMessage());

            return self::FAILURE;
        } finally {
            if (! $this->option('keep-temp')) {
                $filesystem->deleteDirectory($tempPath);
            }
        }

        $this->info('Backup verification completed successfully.');

        return self::SUCCESS;
    }

    /**
     * @param  array<string, mixed>  $databaseConfig
     */
    private function restoreDatabase(array $databaseConfig, string $dumpFile): ConnectionInterface
    {
        $driver = $this->stringOrDefault($databaseConfig['driver'] ?? null, '');
        if ($driver === '') {
            throw new RuntimeException('Database driver is missing from the backup manifest.');
        }

        $configuredDefault = $this->stringOrDefault(config('backup.database.connection'), $this->defaultConnectionName());
        $connectionName = $this->stringOrDefault($databaseConfig['connection'] ?? null, $configuredDefault);

        /** @var array<string, mixed>|null $sourceConfig */
        $sourceConfig = config("database.connections.{$connectionName}");
        if (! is_array($sourceConfig)) {
            throw new RuntimeException(sprintf('Database connection [%s] is not configured.', $connectionName));
        }

        return match ($driver) {
            'mysql' => $this->restoreMysql($connectionName, $sourceConfig, $dumpFile),
            'pgsql' => $this->restorePgsql($connectionName, $sourceConfig, $dumpFile),
            'sqlite' => $this->restoreSqlite($databaseConfig, $dumpFile),
            default => throw new RuntimeException(sprintf('Unsupported database driver [%s] in manifest.', $driver)),
        };
    }

    /**
     * @param  array<string, mixed>  $sourceConfig
     */
    private function restoreMysql(string $connectionName, array $sourceConfig, string $dumpFile): ConnectionInterface
    {
        $verifyConnection = $this->stringOrDefault(config('backup.verify.connection'), 'backup-verify');
        $sourceDatabase = $this->stringOrDefault($sourceConfig['database'] ?? null, 'backup_verification');
        $baseDatabase = $this->stringOrDefault(config('backup.verify.database'), $sourceDatabase);
        $temporaryDatabase = sprintf('%s_%s', $baseDatabase, Str::lower(Str::random(6)));

        $host = $this->stringOrDefault($sourceConfig['host'] ?? null, '127.0.0.1');
        $port = $this->stringOrDefault($sourceConfig['port'] ?? null, '3306');
        $username = $this->stringOrDefault($sourceConfig['username'] ?? null, 'root');
        $password = $this->stringOrDefault($sourceConfig['password'] ?? null, '');
        $charset = $this->stringOrDefault($sourceConfig['charset'] ?? null, 'utf8mb4');
        $collation = $this->stringOrDefault($sourceConfig['collation'] ?? null, 'utf8mb4_unicode_ci');
        $restoreBinary = $this->stringOrDefault(config('backup.database.restore_binary'), 'mysql');

        $pdo = DB::connection($connectionName)->getPdo();
        $pdo->exec(sprintf('DROP DATABASE IF EXISTS `%s`', str_replace('`', '``', $temporaryDatabase)));
        $pdo->exec(sprintf('CREATE DATABASE `%s` CHARACTER SET %s COLLATE %s', str_replace('`', '``', $temporaryDatabase), $charset, $collation));

        $command = sprintf(
            '%s --host=%s --port=%s --user=%s %s < %s',
            escapeshellcmd($restoreBinary),
            escapeshellarg($host),
            escapeshellarg($port),
            escapeshellarg($username),
            escapeshellarg($temporaryDatabase),
            escapeshellarg($dumpFile),
        );

        $process = Process::fromShellCommandline($command, base_path(), $password === '' ? null : ['MYSQL_PWD' => $password]);
        $process->setTimeout(null);

        try {
            $process->mustRun();
        } catch (ProcessFailedException $exception) {
            $pdo->exec(sprintf('DROP DATABASE IF EXISTS `%s`', str_replace('`', '``', $temporaryDatabase)));

            throw $exception;
        }

        $config = $sourceConfig;
        $config['database'] = $temporaryDatabase;
        config(['database.connections.'.$verifyConnection => $config]);

        register_shutdown_function(static function () use ($verifyConnection, $temporaryDatabase, $connectionName): void {
            DB::purge($verifyConnection);
            $pdo = DB::connection($connectionName)->getPdo();
            $pdo->exec(sprintf('DROP DATABASE IF EXISTS `%s`', str_replace('`', '``', $temporaryDatabase)));
        });

        return DB::connection($verifyConnection);
    }

    /**
     * @param  array<string, mixed>  $databaseConfig
     * @param  array<string, mixed>  $sourceConfig
     */
    private function restorePgsql(string $connectionName, array $sourceConfig, string $dumpFile): ConnectionInterface
    {
        $verifyConnection = $this->stringOrDefault(config('backup.verify.connection'), 'backup-verify');
        $sourceDatabase = $this->stringOrDefault($sourceConfig['database'] ?? null, 'backup_verification');
        $baseDatabase = $this->stringOrDefault(config('backup.verify.database'), $sourceDatabase);
        $temporaryDatabase = sprintf('%s_%s', $baseDatabase, Str::lower(Str::random(6)));

        $host = $this->stringOrDefault($sourceConfig['host'] ?? null, '127.0.0.1');
        $port = $this->stringOrDefault($sourceConfig['port'] ?? null, '5432');
        $username = $this->stringOrDefault($sourceConfig['username'] ?? null, 'postgres');
        $password = $this->stringOrDefault($sourceConfig['password'] ?? null, '');
        $restoreBinary = $this->stringOrDefault(config('backup.database.restore_binary'), 'psql');

        $pdo = DB::connection($connectionName)->getPdo();
        $pdo->exec(sprintf('DROP DATABASE IF EXISTS "%s"', str_replace('"', '""', $temporaryDatabase)));
        $pdo->exec(sprintf('CREATE DATABASE "%s"', str_replace('"', '""', $temporaryDatabase)));

        $command = sprintf(
            '%s --host=%s --port=%s --username=%s %s < %s',
            escapeshellcmd($restoreBinary),
            escapeshellarg($host),
            escapeshellarg($port),
            escapeshellarg($username),
            escapeshellarg($temporaryDatabase),
            escapeshellarg($dumpFile),
        );

        $process = Process::fromShellCommandline($command, base_path(), $password === '' ? null : ['PGPASSWORD' => $password]);
        $process->setTimeout(null);

        try {
            $process->mustRun();
        } catch (ProcessFailedException $exception) {
            $pdo->exec(sprintf('DROP DATABASE IF EXISTS "%s"', str_replace('"', '""', $temporaryDatabase)));

            throw $exception;
        }

        $config = $sourceConfig;
        $config['database'] = $temporaryDatabase;
        config(['database.connections.'.$verifyConnection => $config]);

        register_shutdown_function(static function () use ($verifyConnection, $temporaryDatabase, $connectionName): void {
            DB::purge($verifyConnection);
            $pdo = DB::connection($connectionName)->getPdo();
            $pdo->exec(sprintf('DROP DATABASE IF EXISTS "%s"', str_replace('"', '""', $temporaryDatabase)));
        });

        return DB::connection($verifyConnection);
    }

    /**
     * @param  array<string, mixed>  $databaseConfig
     */
    private function restoreSqlite(array $databaseConfig, string $dumpFile): ConnectionInterface
    {
        $verifyConnection = $this->stringOrDefault(config('backup.verify.connection'), 'backup-verify');
        $databaseName = $this->stringOrDefault($databaseConfig['database'] ?? null, 'database.sqlite');
        $baseName = basename($databaseName, '.sqlite');
        $tempDatabase = storage_path('framework'.DIRECTORY_SEPARATOR.$baseName.'-backup-verify-'.Str::lower(Str::random(8)).'.sqlite');

        $filesystem = new Filesystem;
        $filesystem->ensureDirectoryExists(dirname($tempDatabase));
        $filesystem->copy($dumpFile, $tempDatabase);

        $config = [
            'driver' => 'sqlite',
            'database' => $tempDatabase,
            'prefix' => '',
            'foreign_key_constraints' => true,
        ];

        config(['database.connections.'.$verifyConnection => $config]);

        register_shutdown_function(static function () use ($verifyConnection, $tempDatabase): void {
            DB::purge($verifyConnection);
            @unlink($tempDatabase);
        });

        return DB::connection($verifyConnection);
    }

    private function extractMediaArchive(string $mediaArchive, string $tempPath): void
    {
        $extractPath = $tempPath.DIRECTORY_SEPARATOR.'media';
        $filesystem = new Filesystem;
        $filesystem->ensureDirectoryExists($extractPath);

        $process = new Process(['tar', '-xzf', $mediaArchive, '-C', $extractPath], base_path());
        $process->setTimeout(null);
        $process->run();

        if (! $process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }
    }

    /**
     * @param  array<string, int|numeric-string>  $expected
     */
    private function runSanityChecks(ConnectionInterface $connection, array $expected): void
    {
        $userRepository = new UserRepository($connection);
        $productRepository = new ProductRepository($connection);

        $actualUsers = $userRepository->count();
        $actualProducts = $productRepository->count();

        $expectedUsers = (int) ($expected['users'] ?? $actualUsers);
        $expectedProducts = (int) ($expected['products'] ?? $actualProducts);

        if ($actualUsers !== $expectedUsers) {
            throw new RuntimeException(sprintf('User count mismatch: expected %d, got %d.', $expectedUsers, $actualUsers));
        }

        if ($actualProducts !== $expectedProducts) {
            throw new RuntimeException(sprintf('Product count mismatch: expected %d, got %d.', $expectedProducts, $actualProducts));
        }

        $this->info(sprintf('Sanity checks passed (users: %d, products: %d).', $actualUsers, $actualProducts));
    }

    private function defaultConnectionName(): string
    {
        $default = config('database.default');

        return is_string($default) && $default !== '' ? $default : 'mysql';
    }

    private function stringOrDefault(mixed $value, string $default): string
    {
        return is_string($value) && $value !== '' ? $value : $default;
    }
}
