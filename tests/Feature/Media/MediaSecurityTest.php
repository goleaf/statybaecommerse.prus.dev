<?php

declare(strict_types=1);

namespace Tests\Feature\Media;

use App\Models\Product;
use App\Services\MediaService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use InvalidArgumentException;
use Tests\TestCase;

final class MediaSecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_rejects_non_image_uploads(): void
    {
        Storage::fake('public');
        Queue::fake();

        $product = Product::factory()->create();
        $service = app(MediaService::class);
        $file = UploadedFile::fake()->create('payload.php', 5, 'text/x-php');

        $this->expectException(InvalidArgumentException::class);
        $service->upload($product, $file);
    }

    public function test_rejects_oversized_uploads(): void
    {
        Storage::fake('public');
        Queue::fake();
        config()->set('media.max_upload_size', 1000);

        $product = Product::factory()->create();
        $service = app(MediaService::class);
        $file = UploadedFile::fake()->image('giant.jpg', 4000, 4000)->size(2048);

        $this->expectException(InvalidArgumentException::class);
        $service->upload($product, $file);
    }

    public function test_sanitizes_file_names(): void
    {
        Storage::fake('public');
        Queue::fake();

        $product = Product::factory()->create();
        $service = app(MediaService::class);
        $file = UploadedFile::fake()->image('../dangerous<script>.jpg', 800, 600)->size(500);

        $media = $service->upload($product, $file);

        $this->assertStringEndsWith('.jpg', $media->file_name);
        $this->assertStringNotContainsString('<script>', $media->file_name);
        $this->assertStringNotContainsString('..', $media->file_name);
    }
}
