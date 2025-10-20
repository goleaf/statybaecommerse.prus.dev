<?php

declare(strict_types=1);

namespace App\Console\Commands;

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

        if (! $disk->exists($path)) {
            $this->components->error(sprintf('Backup artifact [%s] was not found on disk [%s].', $path, $diskName));

            return self::FAILURE;
        }

        $contents = $disk->get($path);

        if (! is_string($contents)) {
            $this->components->error('Unable to read the backup artifact contents.');

            return self::FAILURE;
        }

        try {
            $payload = json_decode($contents, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $exception) {
            $this->components->error('Unable to decode backup artifact: '.$exception->getMessage());

            return self::FAILURE;
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

                if (! is_numeric($userId)) {
                    return;
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
        $schema->dropIfExists('backup_users');
        $schema->dropIfExists('backup_products');
    }
}
