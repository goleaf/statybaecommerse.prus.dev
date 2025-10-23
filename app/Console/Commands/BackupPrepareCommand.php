<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Product;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use JsonException;

final class BackupPrepareCommand extends Command
{
    /**
     * @var string
     */
    protected $signature = 'backup:prepare {--disk=backups : The filesystem disk where artifacts should be stored}';

    /**
     * @var string
     */
    protected $description = 'Prepare backup artifacts for critical datasets.';

    public function handle(): int
    {
        $diskName = (string) $this->option('disk');
        $disk = Storage::disk($diskName);

        $directory = 'artifacts';
        if (! $disk->exists($directory)) {
            $disk->makeDirectory($directory);
        }

        $users = User::query()
            ->orderBy('id')
            ->get(['id', 'name', 'email', 'preferred_locale'])
            ->map(fn (User $user): array => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'preferred_locale' => $user->preferred_locale,
            ])
            ->all();

        $products = Product::query()
            ->orderBy('id')
            ->get(['id', 'name', 'sku', 'price'])
            ->map(fn (Product $product): array => [
                'id' => $product->id,
                'name' => $product->name,
                'sku' => $product->sku,
                'price' => $product->price,
            ])
            ->all();

        try {
            $disk->put(
                $directory.'/users.json',
                json_encode([
                    'generated_at' => now()->toIso8601String(),
                    'count' => count($users),
                    'records' => $users,
                ], JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT),
            );

            $disk->put(
                $directory.'/products.json',
                json_encode([
                    'generated_at' => now()->toIso8601String(),
                    'count' => count($products),
                    'records' => $products,
                ], JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT),
            );
        } catch (JsonException $exception) {
            $this->error('Unable to encode backup artifacts: '.$exception->getMessage());

            return self::FAILURE;
        }

        $this->info(sprintf(
            'Prepared backup artifacts for %d users and %d products on the %s disk.',
            count($users),
            count($products),
            $diskName,
        ));

        return self::SUCCESS;
    }
}
