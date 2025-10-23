<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use JsonException;

final class BackupVerifyCommand extends Command
{
    /**
     * @var string
     */
    protected $signature = 'backup:verify {path? : Backup directory to verify} {--disk=} {--connection=}';

    /**
     * @var string
     */
    protected $description = 'Verify backup artifacts by loading them into an ephemeral database connection.';

    /**
     * @throws JsonException
     */
    public function handle(): int
    {
        $diskName = $this->resolveDiskName($this->option('disk'));
        $directory = $this->resolveDirectory(config('backup.directory', 'backups'));

        $storage = Storage::disk($diskName);
        $pathArgument = $this->argument('path');
        $path = $this->resolvePath($storage, $directory, is_string($pathArgument) ? $pathArgument : null);
        if ($path === null) {
            $this->components->error('No backup artifacts were found to verify.');

            return self::FAILURE;
        }

        /** @var array<int|string, mixed> $manifest */
        $manifest = $this->readJson($storage, $path.'/manifest.json');
        /** @var array<int, array<string, mixed>> $users */
        $users = $this->normaliseUserRecords($this->readJson($storage, $path.'/users.json'));
        /** @var array<int, array<string, mixed>> $products */
        $products = $this->normaliseProductRecords($this->readJson($storage, $path.'/products.json'));

        $connection = $this->resolveConnectionName($this->option('connection'));
        $this->ensureEphemeralConnection($connection);

        $schema = Schema::connection($connection);
        $schema->dropIfExists('users');
        $schema->dropIfExists('products');

        $schema->create('users', function (Blueprint $table): void {
            $table->unsignedBigInteger('id');
            $table->string('name');
            $table->string('email');
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->primary('id');
        });

        $schema->create('products', function (Blueprint $table): void {
            $table->unsignedBigInteger('id');
            $table->string('name');
            $table->string('sku');
            $table->decimal('price', 12)->nullable();
            $table->integer('stock_quantity')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->primary('id');
        });

        $connectionInstance = DB::connection($connection);
        $connectionInstance->transaction(function () use ($connectionInstance, $products, $users): void {
            if ($users !== []) {
                $connectionInstance->table('users')->insert($users);
            }

            if ($products !== []) {
                $connectionInstance->table('products')->insert($products);
            }
        });

        $userCount = $connectionInstance->table('users')->count();
        $productCount = $connectionInstance->table('products')->count();

        $counts = [];
        if (isset($manifest['counts']) && is_array($manifest['counts'])) {
            $counts = $manifest['counts'];
        }

        $expectedUsers = isset($counts['users']) && is_numeric($counts['users'])
            ? (int) $counts['users']
            : count($users);
        $expectedProducts = isset($counts['products']) && is_numeric($counts['products'])
            ? (int) $counts['products']
            : count($products);

        $this->components->info(sprintf('Verified %d users and %d products.', $userCount, $productCount));

        if ($userCount !== $expectedUsers || $productCount !== $expectedProducts) {
            $this->components->error('Artifact counts did not match manifest expectations.');

            return self::FAILURE;
        }

        return self::SUCCESS;
    }

    private function resolvePath(Filesystem $storage, string $directory, ?string $pathArgument): ?string
    {
        if (is_string($pathArgument) && $pathArgument !== '') {
            $candidate = trim($pathArgument, '/');
            if ($storage->exists($candidate.'/manifest.json')) {
                return $candidate;
            }
        }

        $baseDirectory = $directory === '.' ? null : $directory;
        $directories = $baseDirectory !== null
            ? $storage->directories($baseDirectory)
            : $storage->directories();

        if (empty($directories)) {
            return null;
        }

        rsort($directories);

        foreach ($directories as $candidate) {
            if ($storage->exists($candidate.'/manifest.json')) {
                return $candidate;
            }

            foreach ($storage->directories($candidate) as $nested) {
                if ($storage->exists($nested.'/manifest.json')) {
                    return $nested;
                }
            }
        }

        return null;
    }

    private function ensureEphemeralConnection(string $connection): void
    {
        if (config()->has('database.connections.'.$connection)) {
            DB::purge($connection);

            return;
        }

        config()->set('database.connections.'.$connection, [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
            'foreign_key_constraints' => true,
        ]);

        DB::purge($connection);
    }

    /**
     * @return array<int|string, mixed>
     *
     * @throws JsonException
     */
    private function readJson(Filesystem $storage, string $path): array
    {
        if (! $storage->exists($path)) {
            return [];
        }

        $contents = $storage->get($path);

        $decoded = json_decode((string) $contents, true, 512, JSON_THROW_ON_ERROR);

        return is_array($decoded) ? $decoded : [];
    }

    private function normalizeTimestamp(?string $timestamp): ?string
    {
        if ($timestamp === null || $timestamp === '') {
            return null;
        }

        return Carbon::parse($timestamp)->toDateTimeString();
    }

    private function resolveDiskName(mixed $diskOption): string
    {
        if (is_string($diskOption) && $diskOption !== '') {
            return $diskOption;
        }

        $configured = config('backup.disk');
        if (is_string($configured) && $configured !== '') {
            return $configured;
        }

        $default = config('filesystems.default');

        return is_string($default) && $default !== '' ? $default : 'local';
    }

    private function resolveDirectory(mixed $directory): string
    {
        if (is_string($directory) && $directory !== '') {
            $trimmed = trim($directory, '/');

            return $trimmed === '' ? '.' : $trimmed;
        }

        return 'backups';
    }

    private function resolveConnectionName(mixed $connectionOption): string
    {
        if (is_string($connectionOption) && $connectionOption !== '') {
            return $connectionOption;
        }

        $configured = config('backup.verify.connection');
        if (is_string($configured) && $configured !== '') {
            return $configured;
        }

        return 'sqlite';
    }

    /**
     * @param  array<int|string, mixed>  $users
     * @return array<int, array{id: int, name: string, email: string, created_at: string|null, updated_at: string|null}>
     */
    private function normaliseUserRecords(array $users): array
    {
        $normalised = [];

        foreach ($users as $user) {
            if (! is_array($user)) {
                continue;
            }

            $id = $user['id'] ?? null;
            $name = $user['name'] ?? null;
            $email = $user['email'] ?? null;

            if (! is_numeric($id) || ! is_string($name) || ! is_string($email)) {
                continue;
            }

            $createdAt = isset($user['created_at']) && is_string($user['created_at']) ? $user['created_at'] : null;
            $updatedAt = isset($user['updated_at']) && is_string($user['updated_at']) ? $user['updated_at'] : null;

            $normalised[] = [
                'id' => (int) $id,
                'name' => $name,
                'email' => $email,
                'created_at' => $this->normalizeTimestamp($createdAt),
                'updated_at' => $this->normalizeTimestamp($updatedAt),
            ];
        }

        return $normalised;
    }

    /**
     * @param  array<int|string, mixed>  $products
     * @return array<int, array{id: int, name: string, sku: string, price: float|null, stock_quantity: int|null, created_at: string|null, updated_at: string|null}>
     */
    private function normaliseProductRecords(array $products): array
    {
        $normalised = [];

        foreach ($products as $product) {
            if (! is_array($product)) {
                continue;
            }

            $id = $product['id'] ?? null;
            $name = $product['name'] ?? null;
            $sku = $product['sku'] ?? null;

            if (! is_numeric($id) || ! is_string($name) || ! is_string($sku)) {
                continue;
            }

            $priceValue = $product['price'] ?? null;
            $stockValue = $product['stock_quantity'] ?? null;
            $createdAt = isset($product['created_at']) && is_string($product['created_at']) ? $product['created_at'] : null;
            $updatedAt = isset($product['updated_at']) && is_string($product['updated_at']) ? $product['updated_at'] : null;

            $normalised[] = [
                'id' => (int) $id,
                'name' => $name,
                'sku' => $sku,
                'price' => is_numeric($priceValue) ? (float) $priceValue : null,
                'stock_quantity' => is_numeric($stockValue) ? (int) $stockValue : null,
                'created_at' => $this->normalizeTimestamp($createdAt),
                'updated_at' => $this->normalizeTimestamp($updatedAt),
            ];
        }

        return $normalised;
    }
}
