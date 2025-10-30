<?php

declare(strict_types=1);

namespace Tests\Feature\Filament\Resources;

use App\Filament\Resources\NewsTags\Pages\CreateNewsTag;
use App\Filament\Resources\NewsTags\Pages\EditNewsTag;
use App\Filament\Resources\NewsTags\Pages\ListNewsTags;
use App\Filament\Resources\NewsTags\Pages\ViewNewsTag;
use App\Models\News;
use App\Models\NewsTag;
use App\Models\Translations\NewsTranslation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

final class NewsTagResourceTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        // Ensure Filament resolves the admin panel configuration prior to booting Livewire components.
        $this->resolveAdminPanel();

        // Force a single locale so translated factories output deterministic data for assertions.
        config(['app.locale' => 'en', 'app.fallback_locale' => 'en']);
        app()->setLocale('en');

        // Create an administrator who can access every news tag action without policy friction.
        $this->admin = User::factory()->create([
            'email'    => 'admin@example.com',
            'is_admin' => true,
        ]);

        $this->actingAs($this->admin);
    }

    public function test_list_page_displays_news_tags(): void
    {
        // Seed a single visible news tag so the listing has content to verify.
        $tag = NewsTag::factory()->create([
            'name'       => 'Coverage Tag',
            'slug'       => 'coverage-tag',
            'is_visible' => true,
        ]);

        // Load the table and confirm the seeded tag is presented to the administrator.
        Livewire::test(ListNewsTags::class)
            ->call('loadTable')
            ->assertCanSeeTableRecords([$tag])
            ->searchTable('Coverage Tag')
            ->assertCanSeeTableRecords([$tag]);
    }

    public function test_filters_can_toggle_visibility_and_news_usage(): void
    {
        // Create an inactive tag for the visibility filter.
        $inactiveTag = NewsTag::factory()->inactive()->create([
            'name' => 'Inactive Tag',
            'slug' => 'inactive-tag',
        ]);

        // Create an active tag that is linked to a published news article for the relationship filter.
        $activeTag = NewsTag::factory()->active()->create([
            'name' => 'Active Tag',
            'slug' => 'active-tag',
        ]);

        $news = News::factory()->create([
            'is_visible'   => true,
            'published_at' => now()->subDay(),
        ]);

        NewsTranslation::factory()->create([
            'news_id' => $news->id,
            'locale'  => 'en',
            'title'   => 'Tagged Story',
            'slug'    => 'tagged-story',
        ]);

        $news->tags()->attach($activeTag->id);

        // First confirm the inactive filter hides the active tag but shows the inactive one.
        Livewire::test(ListNewsTags::class)
            ->call('loadTable')
            ->filterTable('inactive')
            ->assertCanSeeTableRecords([$inactiveTag])
            ->assertCanNotSeeTableRecords([$activeTag])
            // Then scope to tags that are associated with at least one news record.
            ->filterTable('with_news')
            ->assertCanSeeTableRecords([$activeTag])
            ->assertCanNotSeeTableRecords([$inactiveTag]);
    }

    public function test_record_actions_toggle_visibility_and_duplicate_tag(): void
    {
        // Persist a tag that starts hidden so we can exercise both activate and deactivate flows.
        $tag = NewsTag::factory()->create([
            'name'       => 'Toggle Tag',
            'slug'       => 'toggle-tag',
            'is_visible' => false,
        ]);

        // Activate the tag and confirm the state change reaches the database.
        Livewire::test(ListNewsTags::class)
            ->call('loadTable')
            ->callTableAction('activate', $tag)
            ->assertHasNoTableActionErrors();

        $this->assertDatabaseHas('news_tags', [
            'id'        => $tag->id,
            'is_visible'=> true,
        ]);

        // Deactivate the same tag and ensure the visibility flag is toggled off again.
        Livewire::test(ListNewsTags::class)
            ->call('loadTable')
            ->callTableAction('deactivate', $tag->fresh())
            ->assertHasNoTableActionErrors();

        $this->assertDatabaseHas('news_tags', [
            'id'        => $tag->id,
            'is_visible'=> false,
        ]);

        // Duplicate the record and verify a copy with the expected suffix exists.
        Livewire::test(ListNewsTags::class)
            ->call('loadTable')
            ->callTableAction('duplicate', $tag->fresh())
            ->assertHasNoTableActionErrors();

        $this->assertDatabaseHas('news_tags', [
            'name' => 'Toggle Tag (Copy)',
            'slug' => 'toggle-tag-copy',
        ]);
    }

    public function test_bulk_actions_handle_mass_visibility_toggles_and_duplication(): void
    {
        // Prepare a small batch of tags so each bulk action can mutate multiple records at once.
        $tags = NewsTag::factory()
            ->count(2)
            ->inactive()
            ->create([
                'color' => '#3B82F6',
            ]);

        // Activate every selected tag via the bulk action helper.
        Livewire::test(ListNewsTags::class)
            ->call('loadTable')
            ->callTableBulkAction('bulk_activate', $tags)
            ->assertHasNoTableBulkActionErrors();

        foreach ($tags as $tag) {
            $this->assertDatabaseHas('news_tags', [
                'id'        => $tag->id,
                'is_visible'=> true,
            ]);
        }

        // Deactivate them again to ensure the complementary bulk action restores the previous state.
        $refreshedTags = $tags->map(static fn (NewsTag $tag): NewsTag => $tag->fresh());

        Livewire::test(ListNewsTags::class)
            ->call('loadTable')
            ->callTableBulkAction('bulk_deactivate', $refreshedTags)
            ->assertHasNoTableBulkActionErrors();

        foreach ($tags as $tag) {
            $this->assertDatabaseHas('news_tags', [
                'id'        => $tag->id,
                'is_visible'=> false,
            ]);
        }

        // Duplicate the batch and confirm a copy exists for each original tag.
        Livewire::test(ListNewsTags::class)
            ->call('loadTable')
            ->callTableBulkAction('bulk_duplicate', $refreshedTags)
            ->assertHasNoTableBulkActionErrors();

        foreach ($tags as $tag) {
            $this->assertDatabaseHas('news_tags', [
                'name' => $tag->name . ' (Copy)',
                'slug' => $tag->slug . '-copy',
            ]);
        }
    }

    public function test_create_edit_and_view_pages_manage_form_state(): void
    {
        // Create an initial tag that we will later edit and view.
        $tag = NewsTag::factory()->create([
            'name'       => 'Editable Tag',
            'slug'       => 'editable-tag',
            'description'=> 'Original description',
        ]);

        // Use the create form to persist a brand-new tag record.
        Livewire::test(CreateNewsTag::class)
            ->fillForm([
                'name'        => 'Form Tag',
                'slug'        => 'form-tag',
                'description' => 'Created from the form',
                'is_visible'  => true,
                'sort_order'  => 5,
                'color'       => '#10B981',
                'translations'=> [],
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('news_tags', [
            'slug' => 'form-tag',
        ]);

        // Update the existing tag and confirm the changes persist.
        Livewire::test(EditNewsTag::class, ['record' => $tag->getRouteKey()])
            ->fillForm([
                'name'        => 'Updated Tag',
                'slug'        => 'updated-tag',
                'description' => 'Updated description',
                'is_visible'  => false,
                'sort_order'  => 9,
                'color'       => '#F59E0B',
                'translations'=> [],
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('news_tags', [
            'id'         => $tag->id,
            'name'       => 'Updated Tag',
            'slug'       => 'updated-tag',
            'is_visible' => false,
        ]);

        // Load the view page and ensure the form state reflects the stored values.
        Livewire::test(ViewNewsTag::class, ['record' => $tag->fresh()->getRouteKey()])
            ->assertFormSet([
                'name' => 'Updated Tag',
                'slug' => 'updated-tag',
            ]);
    }
}
