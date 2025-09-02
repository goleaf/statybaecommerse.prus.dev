<?php declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Product;
use App\Services\Images\GradientImageService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

final class ProductPlaceholdersSeeder extends Seeder
{
    public function run(): void
    {
        $collection = config('shopper.media.storage.collection_name');

        /** @var GradientImageService $generator */
        $generator = app(GradientImageService::class);

        Product::query()
            ->with('media')
            ->whereDoesntHave('media', function ($q) use ($collection) {
                $q->where('collection_name', $collection);
            })
            ->orderBy('id')
            ->chunkById(100, function ($products) use ($generator, $collection): void {
                foreach ($products as $product) {
                    try {
                        $tmp = $generator->generateGradientPng(800, 800);
                        $product
                            ->addMedia($tmp)
                            ->withCustomProperties(['placeholder' => true])
                            ->preservingOriginal()
                            ->toMediaCollection($collection);
                    } catch (\Throwable $e) {
                        Log::warning('Placeholder attach failed', ['product_id' => $product->id, 'error' => $e->getMessage()]);
                    }
                }
            });
    }
}
