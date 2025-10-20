<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Product;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Support\Facades\Storage;
use JsonException;

final class BackupPrepareCommand extends Command
{
    /**
     * @var string
     */
    protected $signature = 'backup:prepare {--disk=} {--directory=}';

    /**
     * @var string
     */
    protected $description = 'Prepare backup artifacts for critical datasets.';

    public function handle(): int
    {
        $diskName = $this->resolveDiskName($this->option('disk'));
        $directory = $this->resolveDirectory($this->option('directory'));

        $storage = Storage::disk($diskName);
        $timestampedPath = $directory.'/'.now()->format('Ymd_His');
        $storage->makeDirectory($timestampedPath);

        $users = User::query()
            ->orderBy('id')
            ->get(['id', 'name', 'email', 'created_at', 'updated_at'])
            ->map(fn (User $user): array => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'created_at' => $user->created_at?->toIso8601String(),
                'updated_at' => $user->updated_at?->toIso8601String(),
            ])
            ->values()
            ->all();

        $products = Product::query()
            ->orderBy('id')
            ->get(['id', 'name', 'sku', 'price', 'stock_quantity', 'created_at', 'updated_at'])
            ->map(fn (Product $product): array => [
                'id' => $product->id,
                'name' => $product->name,
                'sku' => $product->sku,
                'price' => $product->price ? (float) $product->price : null,
                'stock_quantity' => $product->stock_quantity,
                'created_at' => $product->created_at?->toIso8601String(),
                'updated_at' => $product->updated_at?->toIso8601String(),
            ])
            ->values()
            ->all();

        $manifest = [
            'generated_at' => now()->toIso8601String(),
            'counts' => [
                'users' => count($users),
                'products' => count($products),
            ],
            'disk' => $diskName,
            'path' => $timestampedPath,
        ];

        $this->writeJson($storage, $timestampedPath.'/users.json', $users);
        $this->writeJson($storage, $timestampedPath.'/products.json', $products);
        $this->writeJson($storage, $timestampedPath.'/manifest.json', $manifest);

        $this->components->info(sprintf('Backup prepared at [%s] on disk [%s].', $timestampedPath, $diskName));

        return self::SUCCESS;
    }

    /**
     * @param  array<int|string, mixed>  $payload
     *
     * @throws JsonException
     */
    private function writeJson(Filesystem $storage, string $path, array $payload): void
    {
        $storage->put(
            $path,
            json_encode(
                $payload,
                JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
            )
        );
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

    private function resolveDirectory(mixed $directoryOption): string
    {
        if (is_string($directoryOption) && $directoryOption !== '') {
            $trimmed = trim($directoryOption, '/');

            return $trimmed === '' ? '.' : $trimmed;
        }

        $configured = config('backup.directory', 'backups');
        $directory = is_string($configured) ? trim($configured, '/') : 'backups';

        return $directory === '' ? '.' : $directory;
    }
}
