<?php

declare(strict_types=1);

use App\Models\Product;
use App\Services\MediaService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

it('generates configured variants with metadata', function () {
    Storage::fake('public');
    Queue::fake();

    $product = Product::factory()->create();
    $service = app(MediaService::class);

    $file = UploadedFile::fake()->image('sample.jpg', 1600, 1200)->size(1024);
    $media = $service->upload($product, $file);

    $service->processVariants($media);
    $media->refresh();

    $variants = $media->getCustomProperty('variants', []);
    $original = $media->getCustomProperty('original', []);

    expect($variants)->toHaveKeys(['thumb', 'medium', 'large']);
    expect($variants['thumb']['width'])->toBeLessThanOrEqual(180);
    expect($variants['thumb']['height'])->toBeLessThanOrEqual(180);
    expect($variants['large']['format'])->toBe('webp');
    expect($original['width'])->toBeGreaterThan(1000);
    expect(Storage::disk($media->disk)->exists($variants['thumb']['path']))->toBeTrue();
});
