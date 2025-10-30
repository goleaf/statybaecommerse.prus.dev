<?php

declare(strict_types=1);

namespace Tests\Feature\Filament\Resources;

use App\Filament\Resources\SystemSettingCategoryResource\Pages\ListSystemSettingCategories;
use App\Models\SystemSettingCategory;
use App\Models\User;
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

        // Boot the Filament admin context so resource URLs and Livewire components resolve during assertions.
        $this->resolveAdminPanel();

        // Force English locale to avoid translated content differences between local environments.
        config(['app.locale' => 'en', 'app.fallback_locale' => 'en']);
        app()->setLocale('en');

        // Sign in as an administrator so guarded routes and table actions are accessible in the test harness.
        $this->admin = User::factory()->create([
            'email'    => 'admin@example.com',
            'is_admin' => true,
        ]);

        $this->actingAs($this->admin);
    }

    public function test_list_page_shows_existing_category(): void
    {
        // Persist a deterministic category to confirm the System Setting Categories table renders seeded data.
        $category = SystemSettingCategory::factory()->create([
            'name'      => 'Operations',
            'slug'      => 'operations',
            'is_active' => true,
        ]);

        Livewire::test(ListSystemSettingCategories::class)
            ->call('loadTable')
            ->assertCanSeeTableRecords([$category]);
    }

    public function test_duplicate_action_creates_copy_with_suffix(): void
    {
        // Prepare a source category whose duplication behaviour we want to verify inside the table action.
        $category = SystemSettingCategory::factory()->create([
            'name' => 'Logistics',
            'slug' => 'logistics',
        ]);

        Livewire::test(ListSystemSettingCategories::class)
            ->call('loadTable')
            ->callTableAction('duplicate', $category);

        $this->assertDatabaseHas('system_setting_categories', [
            'name' => 'Logistics (Copy)',
            'slug' => 'logistics-copy',
        ]);
    }
}
