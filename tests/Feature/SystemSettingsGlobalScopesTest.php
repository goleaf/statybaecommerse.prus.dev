<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\NotificationTemplate;
use App\Models\Setting;
use App\Models\SystemSetting;
use App\Models\SystemSettingCategory;
use App\Models\Scopes\ActiveScope;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

final class SystemSettingsGlobalScopesTest extends TestCase
{
    use RefreshDatabase;

    public function test_notification_template_model_has_active_scope(): void
    {
        // Create test notification templates
        $activeTemplate = NotificationTemplate::factory()->create(['is_active' => true]);
        $inactiveTemplate = NotificationTemplate::factory()->create(['is_active' => false]);

        // Test that only active templates are returned
        $templates = NotificationTemplate::all();
        
        $this->assertCount(1, $templates);
        $this->assertEquals($activeTemplate->id, $templates->first()->id);

        // Test bypassing scopes
        $allTemplates = NotificationTemplate::withoutGlobalScopes()->get();
        $this->assertCount(2, $allTemplates);
    }

    public function test_setting_model_has_active_scope(): void
    {
        // Create test settings
        $activeSetting = Setting::factory()->create(['is_active' => true]);
        $inactiveSetting = Setting::factory()->create(['is_active' => false]);

        // Test that only active settings are returned
        $settings = Setting::all();
        
        $this->assertCount(1, $settings);
        $this->assertEquals($activeSetting->id, $settings->first()->id);

        // Test bypassing scopes
        $allSettings = Setting::withoutGlobalScopes()->get();
        $this->assertCount(2, $allSettings);
    }

    public function test_system_setting_model_has_active_scope(): void
    {
        // Create test system settings
        $activeSystemSetting = SystemSetting::factory()->create(['is_active' => true]);
        $inactiveSystemSetting = SystemSetting::factory()->create(['is_active' => false]);

        // Test that only active system settings are returned
        $systemSettings = SystemSetting::all();
        
        $this->assertCount(1, $systemSettings);
        $this->assertEquals($activeSystemSetting->id, $systemSettings->first()->id);

        // Test bypassing scopes
        $allSystemSettings = SystemSetting::withoutGlobalScopes()->get();
        $this->assertCount(2, $allSystemSettings);
    }

    public function test_system_setting_category_model_has_active_scope(): void
    {
        // Create test system setting categories
        $activeCategory = SystemSettingCategory::factory()->create(['is_active' => true]);
        $inactiveCategory = SystemSettingCategory::factory()->create(['is_active' => false]);

        // Test that only active categories are returned
        $categories = SystemSettingCategory::all();
        
        $this->assertCount(1, $categories);
        $this->assertEquals($activeCategory->id, $categories->first()->id);

        // Test bypassing scopes
        $allCategories = SystemSettingCategory::withoutGlobalScopes()->get();
        $this->assertCount(2, $allCategories);
    }

    public function test_global_scopes_can_be_combined_with_local_scopes(): void
    {
        // Create test data
        $activeTemplate = NotificationTemplate::factory()->create(['is_active' => true]);
        $inactiveTemplate = NotificationTemplate::factory()->create(['is_active' => false]);

        // Test that global scopes work with local scopes
        $templates = NotificationTemplate::where('name', 'like', '%test%')->get();
        $this->assertCount(0, $templates); // No templates with 'test' in name

        // Test bypassing global scopes with local scopes
        $allTemplates = NotificationTemplate::withoutGlobalScopes()->where('is_active', true)->get();
        $this->assertCount(1, $allTemplates);
        $this->assertEquals($activeTemplate->id, $allTemplates->first()->id);
    }

    public function test_global_scopes_are_applied_to_relationships(): void
    {
        // Create test data with relationships
        $activeCategory = SystemSettingCategory::factory()->create(['is_active' => true]);
        $inactiveCategory = SystemSettingCategory::factory()->create(['is_active' => false]);

        // Test that relationships also apply global scopes
        $categories = SystemSettingCategory::all();
        $this->assertCount(1, $categories);
        $this->assertEquals($activeCategory->id, $categories->first()->id);
    }

    public function test_notification_template_scope_combinations(): void
    {
        // Test different combinations of notification template scopes
        $template1 = NotificationTemplate::factory()->create(['is_active' => true]);
        $template2 = NotificationTemplate::factory()->create(['is_active' => false]);

        // Test bypassing specific scopes
        $allTemplates = NotificationTemplate::withoutGlobalScope(ActiveScope::class)->get();
        $this->assertCount(2, $allTemplates); // All templates regardless of active status
    }

    public function test_setting_scope_combinations(): void
    {
        // Test different combinations of setting scopes
        $setting1 = Setting::factory()->create(['is_active' => true]);
        $setting2 = Setting::factory()->create(['is_active' => false]);

        // Test bypassing specific scopes
        $allSettings = Setting::withoutGlobalScope(ActiveScope::class)->get();
        $this->assertCount(2, $allSettings); // All settings regardless of active status
    }

    public function test_system_setting_scope_combinations(): void
    {
        // Test different combinations of system setting scopes
        $systemSetting1 = SystemSetting::factory()->create(['is_active' => true]);
        $systemSetting2 = SystemSetting::factory()->create(['is_active' => false]);

        // Test bypassing specific scopes
        $allSystemSettings = SystemSetting::withoutGlobalScope(ActiveScope::class)->get();
        $this->assertCount(2, $allSystemSettings); // All system settings regardless of active status
    }

    public function test_system_setting_category_scope_combinations(): void
    {
        // Test different combinations of system setting category scopes
        $category1 = SystemSettingCategory::factory()->create(['is_active' => true]);
        $category2 = SystemSettingCategory::factory()->create(['is_active' => false]);

        // Test bypassing specific scopes
        $allCategories = SystemSettingCategory::withoutGlobalScope(ActiveScope::class)->get();
        $this->assertCount(2, $allCategories); // All categories regardless of active status
    }

    public function test_active_scope_with_system_models(): void
    {
        // Test active scope with different system models
        $activeTemplate = NotificationTemplate::factory()->create(['is_active' => true]);
        $inactiveTemplate = NotificationTemplate::factory()->create(['is_active' => false]);

        $activeSetting = Setting::factory()->create(['is_active' => true]);
        $inactiveSetting = Setting::factory()->create(['is_active' => false]);

        $activeSystemSetting = SystemSetting::factory()->create(['is_active' => true]);
        $inactiveSystemSetting = SystemSetting::factory()->create(['is_active' => false]);

        $activeCategory = SystemSettingCategory::factory()->create(['is_active' => true]);
        $inactiveCategory = SystemSettingCategory::factory()->create(['is_active' => false]);

        // Test that only active records are returned
        $templates = NotificationTemplate::all();
        $this->assertCount(1, $templates);
        $this->assertEquals($activeTemplate->id, $templates->first()->id);

        $settings = Setting::all();
        $this->assertCount(1, $settings);
        $this->assertEquals($activeSetting->id, $settings->first()->id);

        $systemSettings = SystemSetting::all();
        $this->assertCount(1, $systemSettings);
        $this->assertEquals($activeSystemSetting->id, $systemSettings->first()->id);

        $categories = SystemSettingCategory::all();
        $this->assertCount(1, $categories);
        $this->assertEquals($activeCategory->id, $categories->first()->id);

        // Test bypassing active scope
        $allTemplates = NotificationTemplate::withoutGlobalScope(ActiveScope::class)->get();
        $this->assertCount(2, $allTemplates);

        $allSettings = Setting::withoutGlobalScope(ActiveScope::class)->get();
        $this->assertCount(2, $allSettings);

        $allSystemSettings = SystemSetting::withoutGlobalScope(ActiveScope::class)->get();
        $this->assertCount(2, $allSystemSettings);

        $allCategories = SystemSettingCategory::withoutGlobalScope(ActiveScope::class)->get();
        $this->assertCount(2, $allCategories);
    }
}
