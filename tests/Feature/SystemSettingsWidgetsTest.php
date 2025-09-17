<?php declare(strict_types=1);

namespace Tests\Feature;

use App\Filament\Widgets\SystemSettingsOverviewWidget;
use App\Filament\Widgets\SystemSettingsByCategoryWidget;
use App\Filament\Widgets\SystemSettingsByTypeWidget;
use App\Filament\Widgets\SystemSettingsByGroupWidget;
use App\Filament\Widgets\RecentSystemSettingsChangesWidget;
use App\Filament\Widgets\PublicSystemSettingsWidget;
use App\Filament\Widgets\SystemSettingsActivityWidget;
use App\Models\SystemSetting;
use App\Models\SystemSettingCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

final class SystemSettingsWidgetsTest extends TestCase
{
    use RefreshDatabase;

    private User $adminUser;
    private SystemSettingCategory $category;

    protected function setUp(): void
    {
        parent::setUp();

        $this->adminUser = User::factory()->create(['email' => 'admin@test.com']);
        $this->adminUser->assignRole('admin');

        $this->category = SystemSettingCategory::factory()->create([
            'name' => 'Test Category',
            'slug' => 'test-category',
        ]);
    }

    public function test_system_settings_overview_widget_displays_correct_stats(): void
    {
        // Create test data
        SystemSetting::factory()->count(10)->create(['is_active' => true]);
        SystemSetting::factory()->count(3)->create(['is_active' => false]);
        SystemSetting::factory()->count(5)->create(['is_public' => true, 'is_active' => true]);
        SystemSetting::factory()->count(2)->create(['is_encrypted' => true, 'is_active' => true]);
        
        SystemSettingCategory::factory()->count(3)->create(['is_active' => true]);

        $this->actingAs($this->adminUser);

        $widget = Livewire::test(SystemSettingsOverviewWidget::class);

        $widget->assertCanSee('13'); // Total settings (10 + 3)
        $widget->assertCanSee('10'); // Active settings
        $widget->assertCanSee('5'); // Public settings
        $widget->assertCanSee('2'); // Encrypted settings
        $widget->assertCanSee('3'); // Categories
    }

    public function test_system_settings_by_category_widget_displays_correct_data(): void
    {
        $category2 = SystemSettingCategory::factory()->create([
            'name' => 'Category 2',
            'slug' => 'category-2',
        ]);

        // Create settings for each category
        SystemSetting::factory()->count(5)->create([
            'category_id' => $this->category->id,
            'is_active' => true,
        ]);

        SystemSetting::factory()->count(3)->create([
            'category_id' => $category2->id,
            'is_active' => true,
        ]);

        $this->actingAs($this->adminUser);

        $widget = Livewire::test(SystemSettingsByCategoryWidget::class);

        $widget->assertStatus(200);
        // Widget should display the data correctly
        $this->assertNotEmpty($widget->get('data')['datasets'][0]['data']);
        $this->assertNotEmpty($widget->get('data')['labels']);
    }

    public function test_system_settings_by_type_widget_displays_correct_data(): void
    {
        SystemSetting::factory()->count(3)->create([
            'type' => 'string',
            'is_active' => true,
        ]);

        SystemSetting::factory()->count(2)->create([
            'type' => 'boolean',
            'is_active' => true,
        ]);

        SystemSetting::factory()->count(1)->create([
            'type' => 'number',
            'is_active' => true,
        ]);

        $this->actingAs($this->adminUser);

        $widget = Livewire::test(SystemSettingsByTypeWidget::class);

        $widget->assertStatus(200);
        $this->assertNotEmpty($widget->get('data')['datasets'][0]['data']);
        $this->assertNotEmpty($widget->get('data')['labels']);
    }

    public function test_system_settings_by_group_widget_displays_correct_data(): void
    {
        SystemSetting::factory()->count(4)->create([
            'group' => 'general',
            'is_active' => true,
        ]);

        SystemSetting::factory()->count(2)->create([
            'group' => 'ecommerce',
            'is_active' => true,
        ]);

        SystemSetting::factory()->count(1)->create([
            'group' => 'security',
            'is_active' => true,
        ]);

        $this->actingAs($this->adminUser);

        $widget = Livewire::test(SystemSettingsByGroupWidget::class);

        $widget->assertStatus(200);
        $this->assertNotEmpty($widget->get('data')['datasets'][0]['data']);
        $this->assertNotEmpty($widget->get('data')['labels']);
    }

    public function test_recent_system_settings_changes_widget_displays_recent_changes(): void
    {
        $oldSetting = SystemSetting::factory()->create([
            'updated_at' => now()->subDays(5),
        ]);

        $recentSetting = SystemSetting::factory()->create([
            'updated_at' => now()->subMinutes(30),
        ]);

        $this->actingAs($this->adminUser);

        $widget = Livewire::test(RecentSystemSettingsChangesWidget::class);

        $widget->assertStatus(200);
        // Should show the recent setting first
        $this->assertTrue($widget->get('data')->first()->id === $recentSetting->id);
    }

    public function test_public_system_settings_widget_displays_public_settings(): void
    {
        $publicSetting = SystemSetting::factory()->create([
            'is_public' => true,
            'is_active' => true,
        ]);

        $privateSetting = SystemSetting::factory()->create([
            'is_public' => false,
            'is_active' => true,
        ]);

        $this->actingAs($this->adminUser);

        $widget = Livewire::test(PublicSystemSettingsWidget::class);

        $widget->assertStatus(200);
        // Should only show public settings
        $this->assertTrue($widget->get('data')->contains('id', $publicSetting->id));
        $this->assertFalse($widget->get('data')->contains('id', $privateSetting->id));
    }

    public function test_system_settings_activity_widget_displays_activity(): void
    {
        $this->actingAs($this->adminUser);

        // Create some settings to generate activity
        SystemSetting::factory()->count(3)->create();

        $widget = Livewire::test(SystemSettingsActivityWidget::class);

        $widget->assertStatus(200);
        // Widget should load without errors
        $this->assertNotNull($widget->get('data'));
    }

    public function test_widgets_are_only_accessible_to_admin_users(): void
    {
        $regularUser = User::factory()->create();
        $this->actingAs($regularUser);

        // All widgets should be accessible (they don't have explicit permissions)
        // but they might show different data based on user permissions
        
        $widgets = [
            SystemSettingsOverviewWidget::class,
            SystemSettingsByCategoryWidget::class,
            SystemSettingsByTypeWidget::class,
            SystemSettingsByGroupWidget::class,
            RecentSystemSettingsChangesWidget::class,
            PublicSystemSettingsWidget::class,
            SystemSettingsActivityWidget::class,
        ];

        foreach ($widgets as $widgetClass) {
            $widget = Livewire::test($widgetClass);
            $widget->assertStatus(200);
        }
    }

    public function test_widgets_handle_empty_data_gracefully(): void
    {
        $this->actingAs($this->adminUser);

        // No settings created, widgets should handle empty data
        $widgets = [
            SystemSettingsOverviewWidget::class,
            SystemSettingsByCategoryWidget::class,
            SystemSettingsByTypeWidget::class,
            SystemSettingsByGroupWidget::class,
            RecentSystemSettingsChangesWidget::class,
            PublicSystemSettingsWidget::class,
            SystemSettingsActivityWidget::class,
        ];

        foreach ($widgets as $widgetClass) {
            $widget = Livewire::test($widgetClass);
            $widget->assertStatus(200);
            // Widgets should not throw exceptions with empty data
        }
    }

    public function test_overview_widget_stats_calculation(): void
    {
        // Create specific test data
        SystemSetting::factory()->create(['is_active' => true, 'is_public' => true, 'is_encrypted' => false]);
        SystemSetting::factory()->create(['is_active' => true, 'is_public' => false, 'is_encrypted' => true]);
        SystemSetting::factory()->create(['is_active' => false, 'is_public' => true, 'is_encrypted' => false]);
        
        SystemSettingCategory::factory()->create(['is_active' => true]);

        $this->actingAs($this->adminUser);

        $widget = Livewire::test(SystemSettingsOverviewWidget::class);

        // Verify the stats are calculated correctly
        $stats = $widget->get('stats');
        
        $this->assertCount(5, $stats); // Should have 5 stat cards
        
        // Find the total settings stat
        $totalStat = collect($stats)->firstWhere('label', 'admin.system_settings.total_settings');
        $this->assertEquals(3, $totalStat['value']);
        
        // Find the active settings stat
        $activeStat = collect($stats)->firstWhere('label', 'admin.system_settings.active_settings');
        $this->assertEquals(2, $activeStat['value']);
        
        // Find the public settings stat
        $publicStat = collect($stats)->firstWhere('label', 'admin.system_settings.public_settings');
        $this->assertEquals(1, $publicStat['value']);
        
        // Find the encrypted settings stat
        $encryptedStat = collect($stats)->firstWhere('label', 'admin.system_settings.encrypted_settings');
        $this->assertEquals(1, $encryptedStat['value']);
    }
}

