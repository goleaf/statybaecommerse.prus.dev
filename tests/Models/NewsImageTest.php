<?php

declare(strict_types=1);

namespace Tests\Models;

use App\Models\News;
use App\Models\NewsImage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class NewsImageTest extends TestCase
{
    use RefreshDatabase;

    public function test_news_image_has_expected_fillable_and_casts(): void
    {
        // Instantiate the model to inspect its configuration without triggering database interactions.
        $model = new NewsImage;

        // Confirm the fillable attributes protect against unintended mass assignment.
        self::assertSame([
            'news_id',
            'file_path',
            'alt_text',
            'caption',
            'is_featured',
            'sort_order',
            'file_size',
            'mime_type',
            'dimensions',
        ], $model->getFillable());

        // Validate cast definitions to ensure attribute types are converted consistently.
        $casts = $model->getCasts();
        self::assertSame('integer', $casts['news_id']);
        self::assertSame('boolean', $casts['is_featured']);
        self::assertSame('integer', $casts['sort_order']);
        self::assertSame('integer', $casts['file_size']);
        self::assertSame('array', $casts['dimensions']);
    }

    public function test_news_relationship_returns_related_model(): void
    {
        // Create a news record that the image will reference for the belongsTo relation.
        $news = News::factory()->create();

        // Persist the image referencing the created news entry to exercise the relation.
        $image = NewsImage::factory()->for($news)->create();

        // Refresh the model instance to ensure relationships are reloaded from the database.
        $image->refresh();

        // The belongsTo relationship should resolve the same news instance that was persisted.
        self::assertInstanceOf(News::class, $image->news);
        self::assertTrue($news->is($image->news));
    }

    public function test_scopes_filter_and_order_records(): void
    {
        // Create images with distinct featured flags, captions, and sort orders for scope testing.
        $alpha = NewsImage::factory()->create([
            'caption'     => 'Alpha image',
            'sort_order'  => 2,
            'is_featured' => false,
        ]);
        $bravo = NewsImage::factory()->create([
            'caption'     => 'Bravo image',
            'sort_order'  => 1,
            'is_featured' => true,
        ]);
        $bravoDuplicate = NewsImage::factory()->create([
            'caption'     => 'Bravo image',
            'sort_order'  => 3,
            'is_featured' => true,
        ]);

        // Only the records flagged as featured should be returned by the featured scope.
        $featuredIds = NewsImage::query()->featured()->pluck('id')->all();
        self::assertSame([$bravo->id, $bravoDuplicate->id], $featuredIds);

        // The ordered scope should sort by sort_order and then id to keep ordering deterministic.
        $orderedIds = NewsImage::query()->ordered()->pluck('id')->all();
        self::assertSame([$bravo->id, $alpha->id, $bravoDuplicate->id], $orderedIds);

        // The orderedByName scope should apply alphabetical ordering by caption with id as a tiebreaker.
        $orderedByNameIds = NewsImage::query()->orderedByName()->pluck('id')->all();
        self::assertSame([$alpha->id, $bravo->id, $bravoDuplicate->id], $orderedByNameIds);
    }

    public function test_accessors_generate_expected_values(): void
    {
        // Ensure URL helpers build predictable strings by setting the application URL root.
        config(['app.url' => 'https://example.test']);

        // Build a stored image record for accessor testing using controlled attribute values.
        $image = NewsImage::factory()->create([
            'file_path' => 'news-images/gallery/example.jpg',
            'mime_type' => 'image/jpeg',
            'file_size' => 2048,
        ])->fresh();

        // Relative file paths should be prefixed with the storage URL when generating the public URL.
        self::assertSame('https://example.test/storage/news-images/gallery/example.jpg', $image->url);

        // The thumbnail URL should follow the expected naming convention in the same directory.
        self::assertSame(
            'https://example.test/storage/news-images/gallery/thumbnails/example_thumb.jpg',
            $image->thumbnail_url
        );

        // The image helper should report whether the mime type begins with the image prefix.
        self::assertTrue($image->isImage());
        self::assertTrue($image->is_image);

        // File sizes should be converted into human readable units with two decimal places.
        self::assertSame('2.00 KB', $image->file_size_formatted);

        // External URLs must remain untouched when retrieving the computed URL attribute.
        $external = NewsImage::factory()->create([
            'file_path' => 'https://cdn.example.test/images/example.png',
            'mime_type' => 'application/pdf',
        ])->fresh();

        self::assertSame('https://cdn.example.test/images/example.png', $external->url);
        self::assertFalse($external->isImage());
        self::assertFalse($external->is_image);
    }
}
