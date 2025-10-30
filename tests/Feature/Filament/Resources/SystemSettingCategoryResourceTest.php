<?php

declare(strict_types=1);

namespace Tests\Feature\Filament\Resources;

use App\Filament\Resources\SystemSettingCategoryResource\Pages\CreateSystemSettingCategory;
use App\Filament\Resources\SystemSettingCategoryResource\Pages\EditSystemSettingCategory;
use App\Filament\Resources\SystemSettingCategoryResource\Pages\ListSystemSettingCategories;
use App\Models\SystemSettingCategory;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

final class SystemSettingCategoryResourceTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        // Prime Filament for the admin panel context so component policies resolve correctly.
        $this->resolveAdminPanel();

        // Keep locale-dependent assertions deterministic across the suite.
        config(['app.locale' => 'en', 'app.fallback_locale' => 'en']);
        app()->setLocale('en');

        // Seed roles before creating the administrator to satisfy policy checks during tests.
        $this->seed(RolesAndPermissionsSeeder::class);

        // Create and authenticate the canonical administrator used throughout the assertions.
        $this->admin = User::factory()->create([
            'email'    => 'admin@example.com',
            'is_admin' => true,
        ]);

        $this->admin->assignRole('administrator');

        $this->actingAs($this->admin);
    }

    public function test_list_page_displays_categories(): void
    {
        // Seed a visible category so the table has a concrete record to render.
        $category = SystemSettingCategory::factory()->create([
            'name' => 'Primary Settings',
            'slug' => 'primary-settings',
        ]);

        Livewire::test(ListSystemSettingCategories::class)
            ->call('loadTable')
            ->assertCanSeeTableRecords([$category]);
    }

    public function test_admin_can_create_category_with_generated_slug(): void
    {
        // Submit the creation form without an explicit slug to exercise the auto-generation hook.
        Livewire::test(CreateSystemSettingCategory::class)
            ->fillForm([
                'name'        => 'Observability',
                'slug'        => '',
                'description' => 'Monitoring and alerting settings.',
                'icon'        => 'heroicon-o-chart-bar',
                'color'       => '#2563eb',
                'sort_order'  => 5,
                'is_active'   => true,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('system_setting_categories', [
            'name' => 'Observability',
            'slug' => 'observability',
        ]);
    }

    public function test_admin_can_update_category_details(): void
    {
        // Prepare a record to edit so we can verify the Filament form persists updates.
        $category = SystemSettingCategory::factory()->create([
            'name'        => 'Legacy Category',
            'slug'        => 'legacy-category',
            'description' => 'Outdated description.',
        ]);

        Livewire::test(EditSystemSettingCategory::class, ['record' => $category->getKey()])
            ->fillForm([
                'name'        => 'Modern Category',
                'slug'        => 'modern-category',
                'description' => 'Updated copy for the settings section.',
                'icon'        => 'heroicon-o-cog',
                'color'       => '#f97316',
                'sort_order'  => 2,
                'is_active'   => false,
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('system_setting_categories', [
            'id'          => $category->id,
            'name'        => 'Modern Category',
            'slug'        => 'modern-category',
            'description' => 'Updated copy for the settings section.',
            'is_active'   => false,
        ]);
    }

    public function test_duplicate_action_creates_copy(): void
    {
        // Seed a source category that will be duplicated through the list table action.
        $category = SystemSettingCategory::factory()->create([
            'name' => 'Shipping Rules',
            'slug' => 'shipping-rules',
        ]);

        Livewire::test(ListSystemSettingCategories::class)
            ->call('loadTable')
            ->callTableAction('duplicate', $category)
            ->assertHasNoTableActionErrors();

        $this->assertDatabaseHas('system_setting_categories', [
            'name' => 'Shipping Rules (Copy)',
            'slug' => 'shipping-rules-copy',
        ]);
    }

    public function test_bulk_actions_toggle_activation_state(): void
    {
        // Create a batch of inactive categories so the activation bulk action has work to perform.
        $inactive = SystemSettingCategory::factory()->count(2)->create([
            'is_active' => false,
        ]);

        Livewire::test(ListSystemSettingCategories::class)
            ->call('loadTable')
            ->callTableBulkAction('activate', $inactive)
            ->assertHasNoBulkActionErrors();

        $inactive->each(function (SystemSettingCategory $category): void {
            // Reload the record to ensure the activation flag persisted to the database.
            $this->assertTrue($category->fresh()->is_active);
        });

        // Flip the activation flag back to false to confirm the complementary bulk action works as expected.
        Livewire::test(ListSystemSettingCategories::class)
            ->call('loadTable')
            ->callTableBulkAction('deactivate', $inactive)
            ->assertHasNoBulkActionErrors();

        $inactive->each(function (SystemSettingCategory $category): void {
            $this->assertFalse($category->fresh()->is_active);
        });
    }

    public function test_filters_scope_results_by_parent_and_status(): void
    {
        // Build a small hierarchy so the relationship filter has predictable options.
        $parent = SystemSettingCategory::factory()->create([
            'name' => 'Parent Category',
            'slug' => 'parent-category',
        ]);

        $child = SystemSettingCategory::factory()->create([
            'name'      => 'Child Category',
            'slug'      => 'child-category',
            'parent_id' => $parent->id,
            'is_active' => true,
        ]);

        $inactive = SystemSettingCategory::factory()->create([
            'name'      => 'Inactive Child',
            'slug'      => 'inactive-child',
            'parent_id' => $parent->id,
            'is_active' => false,
        ]);

        Livewire::test(ListSystemSettingCategories::class)
            ->call('loadTable')
            ->filterTable('parent_id', (string) $parent->id)
            ->assertCanSeeTableRecords([$child, $inactive])
            ->filterTable('is_active', true)
            ->assertCanSeeTableRecords([$child])
            ->assertCanNotSeeTableRecords([$inactive]);
    }
}

