<?php

declare(strict_types=1);
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Livewire\ProductVariantShowcase;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;

// Ensure database fresh for script? We'll define using sqlite memory? We'll reuse same migration as test? Not replic.

Product::query()->delete();
ProductVariant::query()->delete();

$product = Product::factory()->create([
    'is_visible'   => true,
    'status'       => 'published',
    'published_at' => now(),
]);

$variants = [
    ['available_quantity' => 15, 'track_inventory' => true],
    ['available_quantity' => 2, 'track_inventory' => true],
    ['available_quantity' => 0, 'track_inventory' => true],
];

foreach ($variants as $attributes) {
    ProductVariant::factory()->for($product)->create(array_merge([
        'stock_quantity'    => $attributes['available_quantity'],
        'reserved_quantity' => 0,
        'is_default'        => false,
    ], $attributes));
}

$component = Livewire::test(ProductVariantShowcase::class);

$connection = DB::connection();
$connection->enableQueryLog();
$connection->flushQueryLog();

$component->call('selectProduct', $product->id);

$component->set('productVariants', collect());
$component->set('selectedVariant', null);
if ($component->instance()->selectedProduct) {
    $component->instance()->selectedProduct->setRelation('variants', collect());
}

$connection->flushQueryLog();

$accessors = [
    fn () => $component->instance()->variantCounts['total_variants'],
    fn () => $component->instance()->variantCounts['in_stock'],
    fn () => $component->instance()->variantCounts['low_stock'],
    fn () => $component->instance()->variantCounts['out_of_stock'],
];

$queryCounts = [];

foreach (range(1, count($accessors)) as $limit) {
    $connection->flushQueryLog();

    foreach (array_slice($accessors, 0, $limit) as $accessor) {
        $accessor();
    }

    $queryCounts[] = count($connection->getQueryLog());
}

var_dump($queryCounts);
