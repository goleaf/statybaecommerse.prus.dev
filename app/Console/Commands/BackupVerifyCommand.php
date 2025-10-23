<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Support\Repositories\ProductRepository;
use App\Support\Repositories\UserRepository;
use Illuminate\Console\Command;
use Illuminate\Database\Connection;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Schema\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use JsonException;

final class BackupVerifyCommand extends Command
{
    protected $signature = 'backup:verify {--disk=backups : Storage disk hosting the artifacts} {--path=artifacts/backup.json : Relative artifact path on the disk} {--connection= : Database connection used for verification}';

    protected $description = 'Verify a prepared backup artifact against an ephemeral database connection.';

    public function handle(): int
    {
        $diskName = (string) $this->option('disk');
        $path = (string) $this->option('path');
        $connection = $this->option('connection');
        $defaultConnection = config('database.default');
        $connectionName = is_string($connection) && $connection !== ''
            ? $connection
            : (is_string($defaultConnection) && $defaultConnection !== '' ? $defaultConnection : 'sqlite');
        /** @var non-empty-string $connectionName */
        $disk = Storage::disk($diskName);

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
        }

        $contents = $disk->get($path);

        if (! is_string($contents)) {
            $this->components->error('Unable to read the backup artifact contents.');

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
            $payload = json_decode($contents, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $exception) {
            throw new RuntimeException('Failed to parse backup metadata: ' . $exception->getMessage(), 0, $exception);
        }

        if (! is_array($payload)) {
            $this->components->error('The backup artifact does not contain a valid payload structure.');

            return self::FAILURE;
        }

        $userRecords = array_values(array_filter(
            is_array($payload['users'] ?? null) ? $payload['users'] : [],
            static fn ($item): bool => is_array($item),
        ));

        $productRecords = array_values(array_filter(
            is_array($payload['products'] ?? null) ? $payload['products'] : [],
            static fn ($item): bool => is_array($item),
        ));

        $users = collect($userRecords);
        $products = collect($productRecords);

        /** @var Connection $ephemeral */
        $ephemeral = DB::connection($connectionName);
        $ephemeral->getPdo();

        $schema = Schema::connection($connectionName);

        $this->createBackupTables($schema);

        $ephemeral->transaction(function () use ($ephemeral, $users, $products): void {
            $users->each(static function ($user) use ($ephemeral): void {
                $userId = $user['id'] ?? null;

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

                $ephemeral->table('backup_users')->insert([
                    'id' => (int) $userId,
                    'name' => $user['name'] ?? null,
                    'email' => $user['email'] ?? null,
                    'locale' => $user['locale'] ?? null,
                ]);
            });

            $products->each(static function ($product) use ($ephemeral): void {
                $productId = $product['id'] ?? null;

                if (! is_numeric($productId)) {
                    return;
                }

                $ephemeral->table('backup_products')->insert([
                    'id' => (int) $productId,
                    'name' => $product['name'] ?? null,
                    'slug' => $product['slug'] ?? null,
                    'sku' => $product['sku'] ?? null,
                ]);
            });
        });

        $userCount = (int) $ephemeral->table('backup_users')->count();
        $productCount = (int) $ephemeral->table('backup_products')->count();

        if ($userCount !== $users->count() || $productCount !== $products->count()) {
            $this->components->error('Backup verification failed: counts do not match.');

            return self::FAILURE;
        }

        $this->components->info(sprintf(
            'Backup verified on connection [%s]: %d users, %d products.',
            $connectionName,
            $userCount,
            (int) $productCount,
        ));

        $this->dropBackupTables($schema);

        return self::SUCCESS;
    }

    private function createBackupTables(Builder $schema): void
    {
        $schema->dropIfExists('backup_users');
        $schema->dropIfExists('backup_products');

        $schema->create('backup_users', static function (Blueprint $table): void {
            $table->unsignedBigInteger('id')->primary();
            $table->string('name')->nullable();
            $table->string('email')->nullable();
            $table->string('locale')->nullable();
        });

        $schema->create('backup_products', static function (Blueprint $table): void {
            $table->unsignedBigInteger('id')->primary();
            $table->string('name')->nullable();
            $table->string('slug')->nullable();
            $table->string('sku')->nullable();
        });
    }

    private function dropBackupTables(Builder $schema): void
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
