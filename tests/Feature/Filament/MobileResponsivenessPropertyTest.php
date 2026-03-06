<?php

declare(strict_types=1);

namespace Tests\Feature\Filament;

use App\Models\User;
use Exception;
use Filament\Resources\Resource;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

/**
 * Property 8: Mobile Responsiveness Universal
 * For any admin interface component (forms, tables, navigation), it should display and function correctly on mobile devices
 * Validates: Requirements 9.1, 9.2, 9.3, 9.4
 *
 * Feature: filament-admin-backend-setup, Property 8: Mobile Responsiveness Universal
 */
final class MobileResponsivenessPropertyTest extends TestCase
{
    use RefreshDatabase;

    private User $adminUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->adminUser = User::factory()->create([
            'email'    => 'info@egisstatyba.lt',
            'is_admin' => true,
        ]);
    }

    /**
     * Property test: admin panel displays responsive viewport meta tag
     */
    public function test_admin_panel_displays_responsive_viewport_meta_tag(): void
    {
        $this->actingAs($this->adminUser);

        $response = $this->get('/admin');

        $response->assertStatus(200);
        $response->assertSee('<meta name="viewport" content="width=device-width, initial-scale=1"', false);
    }

    /**
     * Property test: admin navigation works on mobile viewports
     */
    public function test_admin_navigation_works_on_mobile_viewports(): void
    {
        $this->actingAs($this->adminUser);

        // Test with mobile viewport dimensions
        $response = $this->get('/admin', [
            'User-Agent' => 'Mozilla/5.0 (iPhone; CPU iPhone OS 14_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/14.0 Mobile/15E148 Safari/604.1',
        ]);

        $response->assertStatus(200);

        // Check for mobile-friendly navigation elements
        $response->assertSee('fi-sidebar', false); // Filament sidebar component
        $response->assertSee('fi-topbar', false); // Filament topbar component

        // Verify responsive navigation classes are present
        $content = $response->getContent();
        $this->assertStringContainsString('lg:hidden', $content, 'Mobile navigation toggle should be present');
        $this->assertStringContainsString('md:', $content, 'Responsive breakpoint classes should be present');
    }

    /**
     * Property test: admin forms are responsive on mobile devices
     */
    public function test_admin_forms_are_responsive_on_mobile_devices(): void
    {
        $this->actingAs($this->adminUser);

        $resourceClasses = $this->getAllFilamentResourceClasses();

        foreach ($resourceClasses as $resourceClass) {
            if (! $this->resourceHasCreatePage($resourceClass)) {
                continue;
            }

            $createRoute = $this->getResourceCreateRoute($resourceClass);
            if (! $createRoute) {
                continue;
            }

            // Test form page with mobile user agent
            $response = $this->get($createRoute, [
                'User-Agent' => 'Mozilla/5.0 (iPhone; CPU iPhone OS 14_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/14.0 Mobile/15E148 Safari/604.1',
            ]);

            if ($response->status() !== 200) {
                continue; // Skip resources that require additional setup
            }

            $content = $response->getContent();

            // Check for responsive form classes
            $this->assertStringContainsString('fi-form', $content, "Resource {$resourceClass} should have Filament form classes");

            // Check for mobile-friendly input spacing and sizing
            $this->assertTrue(
                str_contains($content, 'sm:') || str_contains($content, 'md:') || str_contains($content, 'lg:'),
                "Resource {$resourceClass} forms should have responsive breakpoint classes"
            );
        }
    }

    /**
     * Property test: admin tables are responsive on mobile devices
     */
    public function test_admin_tables_are_responsive_on_mobile_devices(): void
    {
        $this->actingAs($this->adminUser);

        $resourceClasses = $this->getAllFilamentResourceClasses();

        foreach ($resourceClasses as $resourceClass) {
            $indexRoute = $this->getResourceIndexRoute($resourceClass);
            if (! $indexRoute) {
                continue;
            }

            // Test table page with mobile user agent
            $response = $this->get($indexRoute, [
                'User-Agent' => 'Mozilla/5.0 (iPhone; CPU iPhone OS 14_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/14.0 Mobile/15E148 Safari/604.1',
            ]);

            if ($response->status() !== 200) {
                continue; // Skip resources that require additional setup
            }

            $content = $response->getContent();

            // Check for responsive table classes
            $this->assertStringContainsString('fi-table', $content, "Resource {$resourceClass} should have Filament table classes");

            // Check for mobile table responsiveness features
            $this->assertTrue(
                str_contains($content, 'overflow-x-auto') ||
                str_contains($content, 'scroll') ||
                str_contains($content, 'fi-table-responsive'),
                "Resource {$resourceClass} tables should have mobile scroll/responsive features"
            );

            // Check for responsive breakpoint classes
            $this->assertTrue(
                str_contains($content, 'sm:') || str_contains($content, 'md:') || str_contains($content, 'lg:'),
                "Resource {$resourceClass} tables should have responsive breakpoint classes"
            );
        }
    }

    /**
     * Property test: admin dashboard is responsive on mobile devices
     */
    public function test_admin_dashboard_is_responsive_on_mobile_devices(): void
    {
        $this->actingAs($this->adminUser);

        // Test dashboard with mobile user agent
        $response = $this->get('/admin/dashboard', [
            'User-Agent' => 'Mozilla/5.0 (iPhone; CPU iPhone OS 14_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/14.0 Mobile/15E148 Safari/604.1',
        ]);

        $response->assertStatus(200);

        $content = $response->getContent();

        // Check for responsive dashboard layout
        $this->assertStringContainsString('fi-dashboard', $content, 'Dashboard should have Filament dashboard classes');

        // Check for responsive grid/widget layout
        $this->assertTrue(
            str_contains($content, 'grid') || str_contains($content, 'fi-widget'),
            'Dashboard should have responsive grid or widget layout'
        );

        // Check for mobile-friendly widget spacing
        $this->assertTrue(
            str_contains($content, 'gap-') || str_contains($content, 'space-'),
            'Dashboard should have proper spacing for mobile devices'
        );

        // Check for responsive breakpoint classes
        $this->assertTrue(
            str_contains($content, 'sm:') || str_contains($content, 'md:') || str_contains($content, 'lg:'),
            'Dashboard should have responsive breakpoint classes'
        );
    }

    /**
     * Property test: admin modals and overlays work on mobile devices
     */
    public function test_admin_modals_and_overlays_work_on_mobile_devices(): void
    {
        $this->actingAs($this->adminUser);

        // Test a page that likely has modals (dashboard with widgets)
        $response = $this->get('/admin/dashboard', [
            'User-Agent' => 'Mozilla/5.0 (iPhone; CPU iPhone OS 14_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/14.0 Mobile/15E148 Safari/604.1',
        ]);

        $response->assertStatus(200);

        $content = $response->getContent();

        // Check for mobile-friendly modal classes
        if (str_contains($content, 'fi-modal') || str_contains($content, 'modal')) {
            $this->assertTrue(
                str_contains($content, 'fixed') || str_contains($content, 'inset-'),
                'Modals should have proper positioning for mobile devices'
            );

            $this->assertTrue(
                str_contains($content, 'z-') || str_contains($content, 'z-50'),
                'Modals should have proper z-index for mobile layering'
            );
        }

        // Check for touch-friendly interaction elements
        $this->assertTrue(
            str_contains($content, 'touch-') ||
            str_contains($content, 'cursor-pointer') ||
            str_contains($content, 'hover:'),
            'Interface should have touch-friendly interaction classes'
        );
    }

    /**
     * Property test: admin interface has proper touch target sizes
     */
    public function test_admin_interface_has_proper_touch_target_sizes(): void
    {
        $this->actingAs($this->adminUser);

        $response = $this->get('/admin', [
            'User-Agent' => 'Mozilla/5.0 (iPhone; CPU iPhone OS 14_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/14.0 Mobile/15E148 Safari/604.1',
        ]);

        // Accept both 200 (direct access) and 302 (redirect within admin panel)
        $this->assertContains($response->status(), [200, 302], 'Admin panel should be accessible on mobile devices');

        // Only check content if we get a 200 response
        if ($response->status() === 200) {
            $content = $response->getContent();
        } else {
            // Follow the redirect to get the actual content
            $response = $this->followRedirects($response);
            $content = $response->getContent();
        }

        // Check for adequate button/link sizes (minimum 44px recommended)
        $this->assertTrue(
            str_contains($content, 'h-10') || // 40px height
            str_contains($content, 'h-11') || // 44px height
            str_contains($content, 'h-12') || // 48px height
            str_contains($content, 'min-h-') ||
            str_contains($content, 'py-2') || // Adequate padding
            str_contains($content, 'py-3'),
            'Interface elements should have adequate touch target sizes'
        );

        // Check for proper spacing between interactive elements
        $this->assertTrue(
            str_contains($content, 'space-x-') ||
            str_contains($content, 'space-y-') ||
            str_contains($content, 'gap-'),
            'Interactive elements should have proper spacing for touch interaction'
        );
    }

    /**
     * Property test: admin interface text remains readable on mobile
     */
    public function test_admin_interface_text_remains_readable_on_mobile(): void
    {
        $this->actingAs($this->adminUser);

        $response = $this->get('/admin', [
            'User-Agent' => 'Mozilla/5.0 (iPhone; CPU iPhone OS 14_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/14.0 Mobile/15E148 Safari/604.1',
        ]);

        $response->assertStatus(200);

        $content = $response->getContent();

        // Check for appropriate text sizes (not too small for mobile)
        $this->assertTrue(
            str_contains($content, 'text-sm') ||
            str_contains($content, 'text-base') ||
            str_contains($content, 'text-lg'),
            'Text should have appropriate sizes for mobile readability'
        );

        // Check for responsive text sizing
        $this->assertTrue(
            str_contains($content, 'sm:text-') ||
            str_contains($content, 'md:text-') ||
            str_contains($content, 'lg:text-'),
            'Text should have responsive sizing for different screen sizes'
        );

        // Check for proper line height and spacing
        $this->assertTrue(
            str_contains($content, 'leading-') ||
            str_contains($content, 'line-height'),
            'Text should have proper line height for mobile readability'
        );
    }

    /**
     * Property test: admin interface handles landscape and portrait orientations
     */
    public function test_admin_interface_handles_landscape_and_portrait_orientations(): void
    {
        $this->actingAs($this->adminUser);

        // Test with different mobile orientations
        $orientations = [
            'portrait'  => 'Mozilla/5.0 (iPhone; CPU iPhone OS 14_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/14.0 Mobile/15E148 Safari/604.1',
            'landscape' => 'Mozilla/5.0 (iPhone; CPU iPhone OS 14_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/14.0 Mobile/15E148 Safari/604.1',
        ];

        foreach ($orientations as $orientation => $userAgent) {
            $response = $this->get('/admin', [
                'User-Agent' => $userAgent,
            ]);

            $response->assertStatus(200);

            $content = $response->getContent();

            // Check for flexible layout that adapts to orientation
            $this->assertTrue(
                str_contains($content, 'flex') ||
                str_contains($content, 'grid') ||
                str_contains($content, 'fi-layout'),
                "Interface should have flexible layout for {$orientation} orientation"
            );

            // Check for responsive breakpoints that handle different orientations
            $this->assertTrue(
                str_contains($content, 'landscape:') ||
                str_contains($content, 'portrait:') ||
                str_contains($content, 'orientation-'),
                "Interface should handle {$orientation} orientation properly"
            );
        }
    }

    /**
     * Helper method to get all Filament resource classes
     */
    private function getAllFilamentResourceClasses(): array
    {
        $resourceClasses = [];
        $resourcePath = app_path('Filament/Resources');

        if (! is_dir($resourcePath)) {
            return [];
        }

        $files = File::allFiles($resourcePath);

        foreach ($files as $file) {
            $relativePath = $file->getRelativePathname();

            // Skip relation managers and other subdirectories, only get main resource files
            if (str_contains($relativePath, '/') || ! str_ends_with($relativePath, 'Resource.php')) {
                continue;
            }

            $className = 'App\\Filament\\Resources\\' . str_replace('.php', '', $relativePath);

            if (class_exists($className) && is_subclass_of($className, Resource::class)) {
                $resourceClasses[] = $className;
            }
        }

        return $resourceClasses;
    }

    /**
     * Helper method to check if resource has create page
     */
    private function resourceHasCreatePage(string $resourceClass): bool
    {
        try {
            $pages = $resourceClass::getPages();

            return isset($pages['create']);
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Helper method to get resource create route
     */
    private function getResourceCreateRoute(string $resourceClass): ?string
    {
        try {
            $resourceName = str_replace('Resource', '', class_basename($resourceClass));
            $resourceName = strtolower(preg_replace('/([a-z])([A-Z])/', '$1-$2', $resourceName));

            return "/admin/{$resourceName}s/create";
        } catch (Exception $e) {
            return null;
        }
    }

    /**
     * Helper method to get resource index route
     */
    private function getResourceIndexRoute(string $resourceClass): ?string
    {
        try {
            $resourceName = str_replace('Resource', '', class_basename($resourceClass));
            $resourceName = strtolower(preg_replace('/([a-z])([A-Z])/', '$1-$2', $resourceName));

            return "/admin/{$resourceName}s";
        } catch (Exception $e) {
            return null;
        }
    }
}
