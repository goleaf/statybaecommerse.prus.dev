<?php

declare(strict_types=1);

use App\Models\Product;
use App\Models\ProductImage;
use App\Services\ProductImages\ProductImageWriteService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    Storage::fake('public');
});

it('clones an attached image without reassigning the source owner', function (): void {
    $sourceProduct = Product::factory()->create([
        'status' => 'published',
        'published_at' => now(),
    ]);

    $targetProduct = Product::factory()->create([
        'status' => 'published',
        'published_at' => now(),
    ]);

    $sourceImage = ProductImage::factory()->create([
        'product_id' => $sourceProduct->getKey(),
        'path' => 'product-images/shared-source.jpg',
        'sort_order' => 0,
        'is_default' => true,
    ]);

    $service = app(ProductImageWriteService::class);
    $clonedImage = $service->cloneAttach($targetProduct, $sourceImage);

    expect($sourceImage->fresh()?->product_id)->toBe($sourceProduct->getKey())
        ->and($clonedImage->product_id)->toBe($targetProduct->getKey())
        ->and($clonedImage->path)->toBe('product-images/shared-source.jpg')
        ->and($clonedImage->getKey())->not->toBe($sourceImage->getKey());
});

it('does not delete a shared file path while another image record still references it', function (): void {
    Storage::disk('public')->put('product-images/shared-file.jpg', 'shared-content');

    $product = Product::factory()->create([
        'status' => 'published',
        'published_at' => now(),
    ]);

    $first = ProductImage::factory()->create([
        'product_id' => $product->getKey(),
        'path' => 'product-images/shared-file.jpg',
        'sort_order' => 0,
        'is_default' => true,
    ]);

    $second = ProductImage::factory()->create([
        'product_id' => $product->getKey(),
        'path' => 'product-images/shared-file.jpg',
        'sort_order' => 1,
        'is_default' => false,
    ]);

    $service = app(ProductImageWriteService::class);
    $service->delete($first);

    Storage::disk('public')->assertExists('product-images/shared-file.jpg');
    expect($second->fresh())->not->toBeNull();
});

it('deletes the old file when updating an image with a new uploaded file', function (): void {
    Storage::disk('public')->put('product-images/old-update-image.jpg', 'old-content');

    $product = Product::factory()->create([
        'status' => 'published',
        'published_at' => now(),
    ]);

    $image = ProductImage::factory()->create([
        'product_id' => $product->getKey(),
        'path' => 'product-images/old-update-image.jpg',
        'sort_order' => 0,
        'is_default' => true,
    ]);

    $replacementUpload = UploadedFile::fake()->image('new-update-image.jpg', 1200, 800);

    $service = app(ProductImageWriteService::class);
    $updated = $service->update($image, [
        'path' => $replacementUpload,
        'alt_text' => 'Updated image',
    ]);

    Storage::disk('public')->assertMissing('product-images/old-update-image.jpg');
    Storage::disk('public')->assertExists($updated->path);
    expect($updated->alt_text)->toBe('Updated image');
});
