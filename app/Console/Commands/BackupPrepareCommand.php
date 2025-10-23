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

        return self::SUCCESS;
    }
}
