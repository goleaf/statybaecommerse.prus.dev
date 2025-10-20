<?php

declare(strict_types=1);

namespace Tests\Feature\Media;

use App\Jobs\GenerateMediaVariantsJob;
use App\Models\Product;
use App\Services\MediaService;
use App\Support\Storage\SecureStorage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

final class MediaUploadTest extends TestCase
{
    use RefreshDatabase;

    public function test_upload_generates_variants(): void
    {
        $disk = SecureStorage::disk();
        Storage::fake($disk);
        Queue::fake();

        $product = Product::factory()->create();
        $service = app(MediaService::class);
        $file = UploadedFile::fake()->image('catalog.jpg', 1280, 960)->size(2048);

        $media = $service->upload($product, $file);

        Queue::assertPushed(GenerateMediaVariantsJob::class, function (GenerateMediaVariantsJob $job) use ($media) {
            return $job->mediaId() === $media->getKey();
        });

        // Simulate the queue worker
        (new GenerateMediaVariantsJob($media->getKey(), config('media.variants')))->handle($service);

        $media->refresh();
        $variants = $media->getCustomProperty('variants', []);

        $this->assertArrayHasKey('medium', $variants);
        $this->assertTrue(Storage::disk($media->disk)->exists($variants['medium']['path']));
    }

    public function test_delete_media_removes_original_and_variants(): void
    {
        $disk = SecureStorage::disk();
        Storage::fake($disk);

        $product = Product::factory()->create();
        $service = app(MediaService::class);
        $file = UploadedFile::fake()->image('catalog.jpg', 1024, 1024)->size(1024);

        $media = $service->upload($product, $file);
        $service->processVariants($media);
        $media->refresh();

        $paths = collect($media->getCustomProperty('variants', []))->pluck('path')->push($media->getPathRelativeToRoot());

        $service->deleteMedia($media);

        foreach ($paths as $path) {
            $this->assertFalse(Storage::disk($disk)->exists($path));
        }
    }
}
