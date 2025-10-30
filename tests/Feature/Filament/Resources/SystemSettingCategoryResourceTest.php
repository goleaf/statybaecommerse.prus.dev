<?php

declare(strict_types=1);

namespace Tests\Feature\Filament\Resources;

use App\Filament\Resources\SystemSettingCategories\Pages\CreateSystemSettingCategory;
use App\Filament\Resources\SystemSettingCategories\Pages\EditSystemSettingCategory;
use App\Filament\Resources\SystemSettingCategories\Pages\ListSystemSettingCategories;
use App\Models\SystemSettingCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Livewire\Livewire;
use Tests\TestCase;

final class SystemSettingCategoryResourceTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        // Initialise the Filament admin panel context so Livewire components resolve correctly.
        $this->resolveAdminPanel();

        // Standardise localisation to English to keep generated slugs deterministic across factories.
        config(['app.locale' => 'en', 'app.fallback_locale' => 'en']);
        app()->setLocale('en');

        // Seed an administrator to satisfy the resource's authorization requirements.
        $this->admin = User::factory()->create([
            'email'    => 'admin@example.com',
            'is_admin' => true,
        ]);

        $this->actingAs($this->admin);
    }

    public function test_list_page_displays_system_setting_categories(): void
    {
        // Create a visible category so the listing has a concrete record to render.
        $category = SystemSettingCategory::factory()->create([
            'name'       => 'Primary Settings',
            'slug'       => 'primary-settings',
            'is_active'  => true,
            'sort_order' => 1,
        ]);

        // Hydrate the table dataset and ensure the seeded record appears in the listing.
        Livewire::test(ListSystemSettingCategories::class)
            ->call('loadTable')
            ->assertCanSeeTableRecords([$category])
            ->searchTable('Primary Settings')
            ->assertCanSeeTableRecords([$category]);
    }

    public function test_trashed_filter_and_bulk_restore_flow(): void
    {
        // Persist and soft delete a category so we can exercise the trashed filter and restore bulk action.
        $category = SystemSettingCategory::factory()->create([
            'name' => 'Archived Category',
            'slug' => 'archived-category',
        ]);

        $category->delete();

        // Surface the trashed record via the filter and restore it using the dedicated bulk action.
        Livewire::test(ListSystemSettingCategories::class)
            ->call('loadTable')
            ->filterTable('trashed', 'only')
            ->assertCanSeeTableRecords([$category])
            ->callTableBulkAction('restore', [$category])
            ->assertHasNoTableBulkActionErrors();

        $this->assertDatabaseHas('system_setting_categories', [
            'id'         => $category->id,
            'deleted_at' => null,
        ]);
    }

    public function test_bulk_delete_and_force_delete_remove_records(): void
    {
        // Create a batch of categories to validate both soft delete and force delete bulk actions.
        $categories = SystemSettingCategory::factory()->count(2)->create([
            'is_active'  => true,
            'sort_order' => 5,
        ]);

        // Soft delete the selected categories via the default bulk action.
        Livewire::test(ListSystemSettingCategories::class)
            ->call('loadTable')
            ->callTableBulkAction('delete', $categories)
            ->assertHasNoTableBulkActionErrors();

        foreach ($categories as $category) {
            $this->assertSoftDeleted('system_setting_categories', ['id' => $category->id]);
        }

        // Force delete the same records to confirm permanent removal works as expected.
        $refreshedCategories = $categories->map(static fn (SystemSettingCategory $category): SystemSettingCategory => $category->fresh());

        Livewire::test(ListSystemSettingCategories::class)
            ->call('loadTable')
            ->filterTable('trashed', 'only')
            ->callTableBulkAction('forceDelete', $refreshedCategories)
            ->assertHasNoTableBulkActionErrors();

        foreach ($categories as $category) {
            $this->assertDatabaseMissing('system_setting_categories', ['id' => $category->id]);
        }
    }

    public function test_create_page_persists_new_category(): void
    {
        // Prepare deterministic payload values so database assertions remain straightforward.
        $payload = [
            'name'            => 'Observability',
            'slug'            => 'observability',
            'description'     => 'Settings related to logging and metrics.',
            'icon'            => 'heroicon-o-chart-bar',
            'color'           => 'primary',
            'sort_order'      => 12,
            'is_active'       => true,
            'parent_id'       => null,
            'template'        => 'metrics-overview',
            'metadata'        => null,
            'is_collapsible'  => true,
            'show_in_sidebar' => true,
            'permission'      => 'manage-observability',
            'tags'            => 'metrics,alerts',
        ];

        // Submit the create form and confirm the record is stored without validation errors.
        Livewire::test(CreateSystemSettingCategory::class)
            ->fillForm($payload)
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('system_setting_categories', [
            'slug'       => 'observability',
            'icon'       => 'heroicon-o-chart-bar',
            'color'      => 'primary',
            'is_active'  => true,
            'sort_order' => 12,
        ]);
    }

    public function test_edit_page_updates_existing_category(): void
    {
        // Seed a baseline category that we can mutate through the edit form.
        $category = SystemSettingCategory::factory()->create([
            'name'            => 'Legacy Settings',
            'slug'            => 'legacy-settings',
            'description'     => 'Legacy description',
            'is_collapsible'  => false,
            'show_in_sidebar' => false,
        ]);

        $updated = [
            'name'            => 'Modern Settings',
            'slug'            => 'modern-settings',
            'description'     => 'Updated description',
            'icon'            => 'heroicon-o-cog-6-tooth',
            'color'           => 'success',
            'sort_order'      => 3,
            'is_active'       => false,
            'parent_id'       => null,
            'template'        => 'modern-template',
            'metadata'        => null,
            'is_collapsible'  => true,
            'show_in_sidebar' => true,
            'permission'      => 'manage-modern-settings',
            'tags'            => 'modern,ux',
        ];

        // Execute the edit form save action and verify the persisted record reflects the updates.
        Livewire::test(EditSystemSettingCategory::class, ['record' => $category->getRouteKey()])
            ->fillForm($updated)
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('system_setting_categories', [
            'id'             => $category->id,
            'name'           => 'Modern Settings',
            'slug'           => 'modern-settings',
            'is_active'      => false,
            'sort_order'     => 3,
            'permission'     => 'manage-modern-settings',
        ]);

        // Refresh the model to confirm additional attributes like timestamps were touched by the update cycle.
        $this->assertNotNull(SystemSettingCategory::query()->findOrFail($category->id)->updated_at);
    }

    public function test_slug_is_derived_when_blank(): void
    {
        // Leave the slug empty to confirm the form helper derives one from the provided name.
        Livewire::test(CreateSystemSettingCategory::class)
            ->fillForm([
                'name'            => 'Auto Slug Category',
                'slug'            => '',
                'description'     => null,
                'icon'            => null,
                'color'           => 'primary',
                'sort_order'      => 2,
                'is_active'       => true,
                'parent_id'       => null,
                'template'        => null,
                'metadata'        => null,
                'is_collapsible'  => false,
                'show_in_sidebar' => true,
                'permission'      => null,
                'tags'            => null,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('system_setting_categories', [
            'slug' => Str::slug('Auto Slug Category'),
        ]);
    }
}
