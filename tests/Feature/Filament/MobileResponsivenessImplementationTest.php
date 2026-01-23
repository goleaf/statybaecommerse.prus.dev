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
        // Load translations directly from file to verify they exist
        $translations = include base_path('lang/en/admin.php');

        $this->assertArrayHasKey('navigation', $translations);
        $this->assertArrayHasKey('toggle_menu', $translations['navigation']);
        $this->assertArrayHasKey('close_menu', $translations['navigation']);
        $this->assertEquals('Toggle menu', $translations['navigation']['toggle_menu']);
        $this->assertEquals('Close menu', $translations['navigation']['close_menu']);
    }

    public function test_mobile_translations_exist_in_lithuanian(): void
    {
        // Load translations directly from file to verify they exist
        $translations = include base_path('lang/lt/admin.php');

        $this->assertArrayHasKey('navigation', $translations);
        $this->assertArrayHasKey('toggle_menu', $translations['navigation']);
        $this->assertArrayHasKey('close_menu', $translations['navigation']);
        $this->assertEquals('Perjungti meniu', $translations['navigation']['toggle_menu']);
        $this->assertEquals('Uždaryti meniu', $translations['navigation']['close_menu']);
    }

    public function test_mobile_table_translations_exist(): void
    {
        // Load translations directly from file to verify they exist
        $enTranslations = include base_path('lang/en/admin.php');
        $ltTranslations = include base_path('lang/lt/admin.php');

        // Check English translations - table is at top level
        $this->assertArrayHasKey('table', $enTranslations);
        $this->assertArrayHasKey('toggle_search', $enTranslations['table']);
        $this->assertArrayHasKey('toggle_filters', $enTranslations['table']);
        $this->assertEquals('Toggle search', $enTranslations['table']['toggle_search']);

        // Check Lithuanian translations - table is at top level
        $this->assertArrayHasKey('table', $ltTranslations);
        $this->assertArrayHasKey('toggle_search', $ltTranslations['table']);
        $this->assertArrayHasKey('toggle_filters', $ltTranslations['table']);
        $this->assertEquals('Perjungti paiešką', $ltTranslations['table']['toggle_search']);
    }

    public function test_mobile_form_translations_exist(): void
    {
        // Load translations directly from file to verify they exist
        $enTranslations = include base_path('lang/en/admin.php');
        $ltTranslations = include base_path('lang/lt/admin.php');

        // Check English translations - form is nested under filament
        $this->assertArrayHasKey('filament', $enTranslations);
        $this->assertArrayHasKey('form', $enTranslations['filament']);
        $this->assertArrayHasKey('go_back', $enTranslations['filament']['form']);
        $this->assertArrayHasKey('click_to_upload', $enTranslations['filament']['form']);
        $this->assertEquals('Go back', $enTranslations['filament']['form']['go_back']);

        // Check Lithuanian translations - form is nested under filament
        $this->assertArrayHasKey('filament', $ltTranslations);
        $this->assertArrayHasKey('form', $ltTranslations['filament']);
        $this->assertArrayHasKey('go_back', $ltTranslations['filament']['form']);
        $this->assertArrayHasKey('click_to_upload', $ltTranslations['filament']['form']);
        $this->assertEquals('Grįžti', $ltTranslations['filament']['form']['go_back']);
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
        $this->assertStringContainsString('min-height: 44px', $cssContent);
        // Note: touch-action is in the style blocks within the components, not the main CSS
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
        $this->assertStringContainsString('touch-action: manipulation', $formContent);
        $this->assertStringContainsString('sticky bottom-0', $formContent);
        // Note: font-size: 16px is set via JavaScript for iOS devices
    }
}
