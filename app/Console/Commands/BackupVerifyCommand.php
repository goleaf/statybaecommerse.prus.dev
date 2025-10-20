<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use InvalidArgumentException;
use Throwable;

final class BackupVerifyCommand extends Command
{
    /**
     * @var string
     */
    protected $signature = 'backup:verify
        {--connection=backup : The database connection used to validate artifacts}
        {--disk=backups : The filesystem disk where artifacts are stored}';

    /**
     * @var string
     */
    protected $description = 'Verify backup artifacts by restoring them into an ephemeral database connection.';

    public function handle(): int
    {
        $diskName = (string) $this->option('disk');
        $connectionName = (string) $this->option('connection');

        if ($connectionName === Config::get('database.default')) {
            $this->error('Refusing to verify backups using the default database connection.');

            return self::FAILURE;
        }

        if (! Config::has('database.connections.'.$connectionName)) {
            $this->error(sprintf('Database connection [%s] is not configured.', $connectionName));

            return self::FAILURE;
        }

        $disk = Storage::disk($diskName);

        try {
            $usersPayload = $this->decodeArtifact($disk, 'artifacts/users.json');
            $productsPayload = $this->decodeArtifact($disk, 'artifacts/products.json');
        } catch (Throwable $exception) {
            $this->error($exception->getMessage());

            return self::FAILURE;
        }

        $schema = Schema::connection($connectionName);
        $schema->dropIfExists('users');
        $schema->dropIfExists('products');

        $schema->create('users', function (Blueprint $table): void {
            $table->integer('id')->primary();
            $table->string('name');
            $table->string('email')->nullable();
            $table->string('preferred_locale')->nullable();
            $table->timestamps();
        });

        $schema->create('products', function (Blueprint $table): void {
            $table->integer('id')->primary();
            $table->string('name');
            $table->string('sku');
            $table->decimal('price', 12, 2)->nullable();
            $table->timestamps();
        });

        $connection = DB::connection($connectionName);

        $this->seedTable($connectionName, 'users', $usersPayload['records']);
        $this->seedTable($connectionName, 'products', $productsPayload['records']);

        $userCount = $connection->table('users')->count();
        $productCount = $connection->table('products')->count();

        if ($userCount !== (int) $usersPayload['count']) {
            $this->error(sprintf('User count mismatch. Expected %d, got %d.', $usersPayload['count'], $userCount));

            return self::FAILURE;
        }

        if ($productCount !== (int) $productsPayload['count']) {
            $this->error(sprintf('Product count mismatch. Expected %d, got %d.', $productsPayload['count'], $productCount));

            return self::FAILURE;
        }

        $this->info('Backup artifacts verified successfully.');

        return self::SUCCESS;
    }

    /**
     * @return array{count:int, records:array<int, array{id:int, name?:mixed, email?:mixed, preferred_locale?:mixed, sku?:mixed, price?:mixed}>}
     */
    private function decodeArtifact(FilesystemAdapter $disk, string $path): array
    {
        if (! $disk->exists($path)) {
            throw new InvalidArgumentException(sprintf('Backup artifact [%s] does not exist on the [%s] disk.', $path, $disk->getConfig()['driver'] ?? 'unknown'));
        }

        try {
            $contents = $disk->get($path);
        } catch (FileNotFoundException $exception) {
            throw new InvalidArgumentException($exception->getMessage(), (int) $exception->getCode(), $exception);
        }

        if (! is_string($contents)) {
            throw new InvalidArgumentException(sprintf('Backup artifact [%s] is not readable.', $path));
        }

        $payload = json_decode($contents, true, 512, JSON_THROW_ON_ERROR);

        if (! is_array($payload) || ! isset($payload['count'], $payload['records']) || ! is_array($payload['records'])) {
            throw new InvalidArgumentException(sprintf('Backup artifact [%s] is malformed.', $path));
        }

        if (! is_int($payload['count'])) {
            throw new InvalidArgumentException(sprintf('Backup artifact [%s] is missing a numeric record count.', $path));
        }

        $count = $payload['count'];

        $records = [];
        foreach ($payload['records'] as $record) {
            if (! is_array($record)) {
                throw new InvalidArgumentException(sprintf('Backup artifact [%s] contains an invalid record.', $path));
            }

            if (! array_key_exists('id', $record) || ! is_int($record['id'])) {
                throw new InvalidArgumentException(sprintf('Backup artifact [%s] contains a record without a numeric identifier.', $path));
            }

            $records[] = $record;
        }

        if (count($records) !== $count) {
            throw new InvalidArgumentException(sprintf(
                'Backup artifact [%s] record count mismatch. Expected %d entries, found %d.',
                $path,
                $count,
                count($records)
            ));
        }

        /** @var array<int, array{id:int, name?:mixed, email?:mixed, preferred_locale?:mixed, sku?:mixed, price?:mixed}> $records */
        $records = $records;

        return [
            'count' => $count,
            'records' => $records,
        ];
    }

    /**
     * @param  array<int, array{id:int, name?:mixed, email?:mixed, preferred_locale?:mixed, sku?:mixed, price?:mixed}>  $records
     */
    private function seedTable(string $connectionName, string $table, array $records): void
    {
        if ($records === []) {
            return;
        }

        $connection = DB::connection($connectionName);

        $normalized = [];

        foreach ($records as $record) {
            /** @var array{id:int, name?:mixed, email?:mixed, preferred_locale?:mixed, sku?:mixed, price?:mixed} $record */
            $id = $record['id'];

            if ($table === 'users') {
                $nameValue = $record['name'] ?? '';
                $emailValue = $record['email'] ?? null;
                $localeValue = $record['preferred_locale'] ?? null;

                $normalized[] = [
                    'id' => $id,
                    'name' => is_string($nameValue) ? $nameValue : '',
                    'email' => is_string($emailValue) ? $emailValue : null,
                    'preferred_locale' => is_string($localeValue) ? $localeValue : null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];

                continue;
            }

            $nameValue = $record['name'] ?? '';
            $skuValue = $record['sku'] ?? '';
            $priceValue = $record['price'] ?? null;
            $price = null;

            if (is_int($priceValue) || is_float($priceValue) || (is_string($priceValue) && is_numeric($priceValue))) {
                $price = (float) $priceValue;
            }

            $normalized[] = [
                'id' => $id,
                'name' => is_string($nameValue) ? $nameValue : '',
                'sku' => is_string($skuValue) ? $skuValue : '',
                'price' => $price,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        $connection->table($table)->insert($normalized);
    }
}
