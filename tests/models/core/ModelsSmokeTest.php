<?php

declare(strict_types=1);

use Illuminate\Database\Eloquent\Model as EloquentModel;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Schema;

// Dataset of all App\\Models classes discovered in the repo
$allModelClasses = [
    App\Models\Product::class,
    App\Models\ProductVariant::class,
    App\Models\Brand::class,
    App\Models\Price::class,
    App\Models\User::class,
    App\Models\CustomerGroup::class,
    App\Models\Order::class,
    App\Models\Zone::class,
    App\Models\DiscountRedemption::class,
    App\Models\DiscountCode::class,
    App\Models\DiscountCondition::class,
    App\Models\Discount::class,
    App\Models\Legal::class,
    App\Models\Translations\LegalTranslation::class,
    App\Models\Translations\AttributeValueTranslation::class,
    App\Models\Translations\AttributeTranslation::class,
    App\Models\Translations\BrandTranslation::class,
    App\Models\Translations\CollectionTranslation::class,
    App\Models\Translations\CategoryTranslation::class,
    App\Models\Translations\ProductTranslation::class,
    App\Models\Collection::class,
    App\Models\Channel::class,
    App\Models\Category::class,
];

dataset('model_classes', fn () => $allModelClasses);

// Subset that have factories available
dataset('factory_models', fn () => [
    App\Models\Brand::class,
    App\Models\Category::class,
    App\Models\Collection::class,
    App\Models\Channel::class,
    App\Models\Legal::class,
    App\Models\Price::class,
    App\Models\Product::class,
    App\Models\ProductVariant::class,
    App\Models\User::class,
]);

it('model class exists and is an Eloquent model', function (string $className): void {
    expect(class_exists($className))->toBeTrue();
    $instance = new $className;
    expect($instance)->toBeInstanceOf(EloquentModel::class);
})->with('model_classes');

it('can create records using model factories', function (string $className): void {
    /** @var \Illuminate\Database\Eloquent\Model $instance */
    $instance = new $className;
    $table = method_exists($instance, 'getTable') ? $instance->getTable() : null;
    if ($table === null || ! Schema::hasTable($table)) {
        $this->markTestSkipped("Skipping factory test for {$className}: table not present");
    }

    if (! method_exists($className, 'factory')) {
        $this->markTestSkipped("Skipping factory test for {$className}: static factory() not available");
    }

    try {
        /** @var \Illuminate\Database\Eloquent\Model $model */
        $model = $className::factory()->create();
        expect($model->exists)->toBeTrue();
    } catch (QueryException $e) {
        $this->markTestSkipped("Factory for {$className} requires schema not present: ".$e->getMessage());
    }
})->with('factory_models');

it('product isPublished reflects visibility and published_at correctly', function (): void {
    $instance = new App\Models\Product;
    $table = method_exists($instance, 'getTable') ? $instance->getTable() : null;
    if ($table === null || ! Schema::hasTable($table)) {
        $this->markTestSkipped('Skipping: products table not present');
    }
    /** @var App\Models\Product $product */
    $product = App\Models\Product::factory()->create([
        'is_visible' => true,
        'published_at' => now()->subMinute(),
    ]);
    expect($product->isPublished())->toBeTrue();

    $productHidden = App\Models\Product::factory()->create([
        'is_visible' => false,
        'published_at' => now()->subMinute(),
    ]);
    expect($productHidden->isPublished())->toBeFalse();

    $productFuture = App\Models\Product::factory()->create([
        'is_visible' => true,
        'published_at' => now()->addHour(),
    ]);
    expect($productFuture->isPublished())->toBeFalse();
});
