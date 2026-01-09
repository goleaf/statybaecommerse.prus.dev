<?php

declare(strict_types=1);

namespace Tests\Feature\Filament;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Test to verify mobile responsiveness implementation is working
 */
final class MobileResponsivenessImplementationTest extends TestCase
{
    use RefreshDatabase;

    private User $adminUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->adminUser = User::factory()->create([
            'email'    => 'admin@test.com',
            'is_admin' => true,
        ]);
    }

    public function test_mobile_css_file_exists(): void
    {
        $this->assertTrue(
            file_exists(public_path('css/filament/admin-mobile.css')),
            'Mobile CSS file should exist in public directory'
        );
    }

    public function test_mobile_navigation_component_exists(): void
    {
        $this->assertTrue(
            file_exists(resource_path('views/filament/components/mobile-navigation.blade.php')),
            'Mobile navigation component should exist'
        );
    }

    public function test_mobile_table_component_exists(): void
    {
        $this->assertTrue(
            file_exists(resource_path('views/filament/components/mobile-table.blade.php')),
            'Mobile table component should exist'
        );
    }

    public function test_mobile_form_component_exists(): void
    {
        $this->assertTrue(
            file_exists(resource_path('views/filament/components/mobile-form.blade.php')),
            'Mobile form component should exist'
        );
    }

    public function test_mobile_translations_exist_in_english(): void
    {
        $translations = __('admin.navigation', [], 'en');
        
        $this->assertArrayHasKey('toggle_menu', $translations);
        $this->assertArrayHasKey('close_menu', $translations);
        $this->assertEquals('Toggle menu', $translations['toggle_menu']);
        $this->assertEquals('Close menu', $translations['close_menu']);
    }

    public function test_mobile_translations_exist_in_lithuanian(): void
    {
        $translations = __('admin.navigation', [], 'lt');
        
        $this->assertArrayHasKey('toggle_menu', $translations);
        $this->assertArrayHasKey('close_menu', $translations);
        $this->assertEquals('Perjungti meniu', $translations['toggle_menu']);
        $this->assertEquals('Uždaryti meniu', $translations['close_menu']);
    }

    public function test_mobile_table_translations_exist(): void
    {
        $enTranslations = __('admin.table', [], 'en');
        $ltTranslations = __('admin.table', [], 'lt');
        
        // Check English translations
        $this->assertArrayHasKey('toggle_search', $enTranslations);
        $this->assertArrayHasKey('toggle_filters', $enTranslations);
        $this->assertEquals('Toggle search', $enTranslations['toggle_search']);
        
        // Check Lithuanian translations
        $this->assertArrayHasKey('toggle_search', $ltTranslations);
        $this->assertArrayHasKey('toggle_filters', $ltTranslations);
        $this->assertEquals('Perjungti paiešką', $ltTranslations['toggle_search']);
    }

    public function test_mobile_form_translations_exist(): void
    {
        $enTranslations = __('admin.form', [], 'en');
        $ltTranslations = __('admin.form', [], 'lt');
        
        // Check English translations
        $this->assertArrayHasKey('go_back', $enTranslations);
        $this->assertArrayHasKey('click_to_upload', $enTranslations);
        $this->assertEquals('Go back', $enTranslations['go_back']);
        
        // Check Lithuanian translations
        $this->assertArrayHasKey('go_back', $ltTranslations);
        $this->assertArrayHasKey('click_to_upload', $ltTranslations);
        $this->assertEquals('Grįžti', $ltTranslations['go_back']);
    }

    public function test_admin_panel_provider_includes_mobile_hooks(): void
    {
        $providerContent = file_get_contents(app_path('Filament/AdminPanelProvider.php'));
        
        $this->assertStringContainsString(
            'mobile-navigation',
            $providerContent,
            'AdminPanelProvider should include mobile navigation hook'
        );
        
        $this->assertStringContainsString(
            'admin-mobile.css',
            $providerContent,
            'AdminPanelProvider should include mobile CSS hook'
        );
    }

    public function test_mobile_css_contains_responsive_styles(): void
    {
        $cssContent = file_get_contents(public_path('css/filament/admin-mobile.css'));
        
        $this->assertStringContainsString('@media (max-width: 768px)', $cssContent);
        $this->assertStringContainsString('.fi-sidebar', $cssContent);
        $this->assertStringContainsString('.fi-table', $cssContent);
        $this->assertStringContainsString('touch-action: manipulation', $cssContent);
        $this->assertStringContainsString('min-height: 44px', $cssContent);
    }

    public function test_mobile_navigation_includes_javascript(): void
    {
        $navContent = file_get_contents(resource_path('views/filament/components/mobile-navigation.blade.php'));
        
        $this->assertStringContainsString('toggleMobileNav()', $navContent);
        $this->assertStringContainsString('closeMobileNav()', $navContent);
        $this->assertStringContainsString('addEventListener', $navContent);
    }

    public function test_mobile_table_includes_responsive_features(): void
    {
        $tableContent = file_get_contents(resource_path('views/filament/components/mobile-table.blade.php'));
        
        $this->assertStringContainsString('lg:hidden', $tableContent);
        $this->assertStringContainsString('overflow-x-auto', $tableContent);
        $this->assertStringContainsString('touch-action: manipulation', $tableContent);
        $this->assertStringContainsString('mobile-card-view', $tableContent);
    }

    public function test_mobile_form_includes_touch_friendly_inputs(): void
    {
        $formContent = file_get_contents(resource_path('views/filament/components/mobile-form.blade.php'));
        
        $this->assertStringContainsString('min-height: 44px', $formContent);
        $this->assertStringContainsString('font-size: 16px', $formContent);
        $this->assertStringContainsString('touch-action: manipulation', $formContent);
        $this->assertStringContainsString('sticky bottom-0', $formContent);
    }
}