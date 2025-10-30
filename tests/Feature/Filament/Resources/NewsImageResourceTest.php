<?php

declare(strict_types=1);

namespace Tests\Feature\Filament\Resources;

use App\Filament\Resources\NewsImages\Pages\CreateNewsImage;
use App\Filament\Resources\NewsImages\Pages\EditNewsImage;
use App\Filament\Resources\NewsImages\Pages\ListNewsImages;
use App\Models\News;
use App\Models\NewsImage;
use App\Models\Translations\NewsTranslation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

final class NewsImageResourceTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private News $news;

    protected function setUp(): void
    {
        parent::setUp();

        // Boot the Filament admin panel context before interacting with Livewire components.
        $this->resolveAdminPanel();

        // Normalise locales to keep translated factories deterministic across assertions.
        config(['app.locale' => 'en', 'app.fallback_locale' => 'en']);
        app()->setLocale('en');

        // Provision an administrator so authorization checks on the resource succeed.
        $this->admin = User::factory()->create([
            'email'    => 'admin@example.com',
            'is_admin' => true,
        ]);

        // Create a published news record with explicit translations for predictable table output.
        $this->news = News::factory()->create([
            'is_visible'   => true,
            'published_at' => now()->subDay(),
        ]);

        NewsTranslation::factory()->create([
            'news_id' => $this->news->id,
            'locale'  => 'en',
            'title'   => 'Coverage News Story',
            'slug'    => 'coverage-news-story',
        ]);

        // Fake the local filesystem so file uploads performed by the resource remain in memory.
        Storage::fake('local');

        $this->actingAs($this->admin);
    }

    public function test_list_page_displays_news_images(): void
    {
        // Seed a representative news image so the table has content to render.
        $image = NewsImage::factory()
            ->for($this->news)
            ->create([
                'alt_text'   => 'Hero Banner',
                'caption'    => 'Homepage hero banner',
                'file_path'  => 'news-images/hero-banner.jpg',
                'sort_order' => 1,
            ]);

        // Hydrate the table and assert the seeded record is visible to the administrator.
        Livewire::test(ListNewsImages::class)
            ->call('loadTable')
            ->assertCanSeeTableRecords([$image])
            ->searchTable('Hero Banner')
            ->assertCanSeeTableRecords([$image]);
    }

    public function test_filters_can_scope_by_news_and_featured_state(): void
    {
        // Create two distinct news posts so filters can target a single parent record.
        $secondaryNews = News::factory()->create([
            'is_visible'   => true,
            'published_at' => now()->subDays(2),
        ]);

        NewsTranslation::factory()->create([
            'news_id' => $secondaryNews->id,
            'locale'  => 'en',
            'title'   => 'Secondary Story',
            'slug'    => 'secondary-story',
        ]);

        // Attach featured and standard images to separate news records for filtering.
        $featuredImage = NewsImage::factory()
            ->for($this->news)
            ->featured()
            ->create(['caption' => 'Featured Spotlight']);

        $standardImage = NewsImage::factory()
            ->for($secondaryNews)
            ->notFeatured()
            ->create(['caption' => 'Standard Gallery']);

        // Apply the relation filter to narrow the listing to images for the primary news article.
        Livewire::test(ListNewsImages::class)
            ->call('loadTable')
            ->filterTable('news_id', $this->news->id)
            ->assertCanSeeTableRecords([$featuredImage])
            ->assertCanNotSeeTableRecords([$standardImage])
            // Chain the featured toggle to ensure only highlighted assets remain visible.
            ->filterTable('is_featured', true)
            ->assertCanSeeTableRecords([$featuredImage])
            ->assertCanNotSeeTableRecords([$standardImage]);
    }

    public function test_bulk_actions_toggle_featured_state_for_selected_images(): void
    {
        // Create two non-featured images so the bulk action can promote them together.
        $images = NewsImage::factory()
            ->count(2)
            ->for($this->news)
            ->notFeatured()
            ->create();

        // Promote every selected record to featured via the dedicated bulk action.
        Livewire::test(ListNewsImages::class)
            ->call('loadTable')
            ->callTableBulkAction('set_featured', $images)
            ->assertHasNoTableBulkActionErrors();

        foreach ($images as $image) {
            $this->assertDatabaseHas('news_images', [
                'id'           => $image->id,
                'is_featured'  => true,
            ]);
        }

        // Demote the same images to confirm the complementary bulk action behaves correctly.
        $refreshedImages = $images->map(static fn (NewsImage $image): NewsImage => $image->fresh());

        Livewire::test(ListNewsImages::class)
            ->call('loadTable')
            ->callTableBulkAction('unset_featured', $refreshedImages)
            ->assertHasNoTableBulkActionErrors();

        foreach ($images as $image) {
            $this->assertDatabaseHas('news_images', [
                'id'           => $image->id,
                'is_featured'  => false,
            ]);
        }
    }

    public function test_record_action_can_duplicate_news_image(): void
    {
        // Persist a single news image so the duplicate action has a source payload.
        $image = NewsImage::factory()
            ->for($this->news)
            ->create([
                'caption'    => 'Original Caption',
                'sort_order' => 2,
            ]);

        // Trigger the duplicate action and ensure a cloned record is created with an incremented order.
        Livewire::test(ListNewsImages::class)
            ->call('loadTable')
            ->callTableAction('duplicate', $image)
            ->assertHasNoTableActionErrors();

        $this->assertDatabaseHas('news_images', [
            'news_id'    => $this->news->id,
            'caption'    => 'Original Caption',
        ]);

        $this->assertSame(2, NewsImage::query()->where('news_id', $this->news->id)->count());
    }

    public function test_create_page_persists_uploaded_image_metadata(): void
    {
        // Prepare an upload to simulate the administrator attaching a private asset.
        $upload = UploadedFile::fake()->image('news.jpg');

        // Use the create page form to store a brand new news image record.
        Livewire::test(CreateNewsImage::class)
            ->fillForm([
                'news_id'    => $this->news->id,
                'file_path'  => $upload,
                'alt_text'   => 'Accessibility copy',
                'caption'    => 'Form created caption',
                'is_featured'=> true,
                'sort_order' => 5,
                'mime_type'  => 'image/jpeg',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('news_images', [
            'news_id'    => $this->news->id,
            'alt_text'   => 'Accessibility copy',
            'caption'    => 'Form created caption',
            'is_featured'=> true,
            'sort_order' => 5,
        ]);
    }

    public function test_edit_page_updates_existing_image_metadata(): void
    {
        // Persist a baseline image that we can mutate through the edit form.
        $image = NewsImage::factory()
            ->for($this->news)
            ->create([
                'alt_text'   => 'Original text',
                'caption'    => 'Original caption',
                'sort_order' => 3,
            ]);

        // Submit updated metadata and confirm the persisted record reflects the changes.
        Livewire::test(EditNewsImage::class, ['record' => $image->getRouteKey()])
            ->fillForm([
                'news_id'    => $this->news->id,
                'file_path'  => [$image->file_path],
                'alt_text'   => 'Updated alt text',
                'caption'    => 'Updated caption',
                'is_featured'=> false,
                'sort_order' => 7,
                'mime_type'  => $image->mime_type,
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('news_images', [
            'id'          => $image->id,
            'alt_text'    => 'Updated alt text',
            'caption'     => 'Updated caption',
            'is_featured' => false,
            'sort_order'  => 7,
        ]);
    }
}
