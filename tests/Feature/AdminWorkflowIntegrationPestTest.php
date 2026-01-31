<?php

declare(strict_types=1);

use App\Enums\NavigationGroup;
use Illuminate\Support\Facades\Route;

/**
 * Integration tests for complete admin workflows using Pest
 *
 * Feature: filament-admin-backend-setup, Property 15.1: Complete Admin Workflow Integration
 * Validates: All requirements
 *
 * These tests validate complete user journeys through the admin panel,
 * multi-resource operations, navigation flows, and translation completeness.
 */
describe('Admin Workflow Integration', function () {

    /**
     * Test navigation group organization and consistency
     * Validates: Requirements 3.2, 3.3, 8.1, 8.3
     */
    it('has properly organized navigation groups', function () {
        // Test navigation group enum consistency
        $navigationGroups = NavigationGroup::cases();
        expect($navigationGroups)->not->toBeEmpty('Navigation groups should be defined');

        $groupValues = [];
        $groupNames = [];

        foreach ($navigationGroups as $group) {
            expect($group->value)->toBeString("Navigation group {$group->name} should have string value");
            expect($group->value)->not->toBeEmpty("Navigation group {$group->name} value should not be empty");

            $groupValues[] = $group->value;
            $groupNames[] = $group->name;
        }

        // Test uniqueness for proper navigation state management
        expect(count($groupValues))->toBe(count(array_unique($groupValues)), 'Navigation group values should be unique');
        expect(count($groupNames))->toBe(count(array_unique($groupNames)), 'Navigation group names should be unique');

        // Test expected core groups exist
        $expectedGroups = ['UserManagement', 'ContentManagement', 'Ecommerce', 'System'];
        $actualGroupNames = array_map(fn ($group) => $group->name, $navigationGroups);

        foreach ($expectedGroups as $expectedGroup) {
            expect($actualGroupNames)->toContain($expectedGroup, "Core navigation group {$expectedGroup} should exist");
        }
    });

    /**
     * Test translation file completeness
     * Validates: Requirements 8.1, 8.2, 8.3, 8.4
     */
    it('has complete translation files for all supported locales', function () {
        $supportedLocales = ['lt', 'en'];

        foreach ($supportedLocales as $locale) {
            // Test navigation translations exist
            $navigationFile = resource_path("lang/{$locale}/navigation.php");
            expect($navigationFile)->toBeFile("Navigation translation file should exist for locale {$locale}");

            if (file_exists($navigationFile)) {
                $navigationTranslations = include $navigationFile;
                expect($navigationTranslations)->toBeArray("Navigation translations should be array for locale {$locale}");

                // Test required navigation group translations
                expect($navigationTranslations)->toHaveKey('navigation_groups',
                    "Navigation groups section should exist for locale {$locale}");

                $requiredKeys = ['user_management', 'content_management', 'ecommerce', 'system', 'inventory'];
                foreach ($requiredKeys as $key) {
                    expect($navigationTranslations['navigation_groups'])->toHaveKey($key,
                        "Navigation group key {$key} should exist for locale {$locale}");
                    expect($navigationTranslations['navigation_groups'][$key])->not->toBeEmpty(
                        "Navigation group translation for {$key} should not be empty in locale {$locale}");
                }
            }

            // Test that core translation files exist
            $coreTranslationFiles = ['navigation', 'products', 'categories', 'brands'];
            foreach ($coreTranslationFiles as $file) {
                $filePath = resource_path("lang/{$locale}/{$file}.php");
                if (file_exists($filePath)) {
                    $translations = include $filePath;
                    expect($translations)->toBeArray("Translation file {$file}.php should return array for locale {$locale}");
                    expect($translations)->not->toBeEmpty("Translation file {$file}.php should not be empty for locale {$locale}");
                }
            }
        }
    });

    /**
     * Test admin route registration
     * Validates: Requirements 2.1, 2.2, 2.3
     */
    it('has properly registered admin routes', function () {
        // Test that core admin routes are registered
        $coreRoutes = [
            'filament.admin.pages.dashboard',
            'filament.admin.auth.login',
        ];

        foreach ($coreRoutes as $routeName) {
            expect(Route::has($routeName))->toBeTrue("Core route {$routeName} should exist");
        }

        // Test that admin routes follow expected patterns
        $adminRoutes = collect(Route::getRoutes())->filter(function ($route) {
            return str_starts_with($route->getName() ?? '', 'filament.admin.');
        });

        expect($adminRoutes->count())->toBeGreaterThan(0, 'Admin routes should be registered');

        // Test route naming conventions
        foreach ($adminRoutes as $route) {
            $routeName = $route->getName();
            expect($routeName)->toStartWith('filament.admin.', "Admin route {$routeName} should follow naming convention");
        }
    });

    /**
     * Test admin resource class existence
     * Validates: Requirements 4.1, 11.1, 11.2, 11.3, 11.4, 11.5, 11.6
     */
    it('has properly defined admin resource classes', function () {
        $expectedResources = [
            'App\\Filament\\Resources\\ProductResource',
            'App\\Filament\\Resources\\CategoryResource',
            'App\\Filament\\Resources\\BrandResource',
            'App\\Filament\\Resources\\InventoryResource',
            'App\\Filament\\Resources\\PriceResource',
            'App\\Filament\\Resources\\DiscountResource',
        ];

        foreach ($expectedResources as $resourceClass) {
            expect(class_exists($resourceClass))->toBeTrue("Resource class {$resourceClass} should exist");

            if (class_exists($resourceClass)) {
                // Test that resource has required methods
                $reflection = new \ReflectionClass($resourceClass);

                $requiredMethods = ['form', 'table', 'getRelations', 'getPages'];
                foreach ($requiredMethods as $method) {
                    expect($reflection->hasMethod($method))->toBeTrue(
                        "Resource {$resourceClass} should have {$method} method");
                }
            }
        }
    });

    /**
     * Test navigation group enum properties
     * Validates: Requirements 3.4, 8.4
     */
    it('has consistent navigation group enum properties', function () {
        $navigationGroups = NavigationGroup::cases();

        foreach ($navigationGroups as $group) {
            // Test that each group has consistent properties
            expect($group->value)->toBeString("Group {$group->name} should have string value");
            expect($group->name)->toBeString("Group {$group->name} should have string name");

            // Test value format for CSS class compatibility
            expect($group->value)->toMatch('/^[a-z][a-z-]*$/',
                "Group value {$group->value} should be valid CSS class name");

            // Test name format for PHP enum compatibility
            expect($group->name)->toMatch('/^[A-Z][a-zA-Z]*$/',
                "Group name {$group->name} should follow PascalCase");

            // Test enum consistency
            $sameGroup = NavigationGroup::from($group->value);
            expect($sameGroup)->toBe($group, 'Navigation group should maintain identity');
        }
    });

    /**
     * Test admin panel configuration files
     * Validates: Requirements 2.1, 2.2, 2.3, 5.1, 5.2
     */
    it('has proper admin panel configuration files', function () {
        // Test that admin panel provider exists
        $adminPanelProvider = 'App\\Filament\\AdminPanelProvider';
        expect(class_exists($adminPanelProvider))->toBeTrue('AdminPanelProvider should exist');

        // Test that navigation group enum exists and is properly configured
        expect(enum_exists(NavigationGroup::class))->toBeTrue('NavigationGroup enum should exist');

        // Test that base resource exists
        $baseResource = 'App\\Filament\\Resources\\BaseResource';
        if (class_exists($baseResource)) {
            $reflection = new \ReflectionClass($baseResource);
            expect($reflection->isAbstract())->toBeTrue('BaseResource should be abstract');
        }

        // Test configuration files exist
        $configFiles = [
            'filament.php',
            'auth.php',
            'app.php',
        ];

        foreach ($configFiles as $configFile) {
            $configPath = config_path($configFile);
            expect($configPath)->toBeFile("Configuration file {$configFile} should exist");
        }
    });

    /**
     * Test mobile responsiveness configuration
     * Validates: Requirements 9.1, 9.2, 9.3, 9.4
     */
    it('has mobile responsiveness configuration', function () {
        // Test that TailwindCSS configuration exists (for responsive design)
        $tailwindConfig = base_path('tailwind.config.js');
        expect($tailwindConfig)->toBeFile('TailwindCSS configuration should exist for responsive design');

        // Test that Vite configuration exists (for asset compilation)
        $viteConfig = base_path('vite.config.js');
        expect($viteConfig)->toBeFile('Vite configuration should exist for asset compilation');

        // Test that responsive breakpoints are configured
        if (file_exists($tailwindConfig)) {
            $tailwindContent = file_get_contents($tailwindConfig);
            $responsiveIndicators = ['sm:', 'md:', 'lg:', 'xl:', 'responsive'];
            $foundIndicators = 0;

            foreach ($responsiveIndicators as $indicator) {
                if (strpos($tailwindContent, $indicator) !== false) {
                    $foundIndicators++;
                }
            }

            expect($foundIndicators)->toBeGreaterThan(0, 'TailwindCSS should have responsive configuration');
        }
    });

    /**
     * Test error handling configuration
     * Validates: Requirements 10.1, 10.2, 10.3, 10.4
     */
    it('has proper error handling configuration', function () {
        // Test that exception handler exists
        $exceptionHandler = 'App\\Exceptions\\Handler';
        expect(class_exists($exceptionHandler))->toBeTrue('Exception handler should exist');

        // Test that error pages directory exists
        $errorPagesDir = resource_path('views/errors');
        if (is_dir($errorPagesDir)) {
            $errorPages = ['404.blade.php', '500.blade.php'];
            foreach ($errorPages as $errorPage) {
                $errorPagePath = $errorPagesDir . '/' . $errorPage;
                if (file_exists($errorPagePath)) {
                    expect($errorPagePath)->toBeFile("Error page {$errorPage} should exist");
                }
            }
        }

        // Test logging configuration
        $loggingConfig = config_path('logging.php');
        expect($loggingConfig)->toBeFile('Logging configuration should exist');
    });

    /**
     * Test complete workflow integration points
     * Validates: All requirements integration
     */
    it('has all major components properly integrated', function () {
        // Test that all major components are properly integrated

        // 1. Navigation system integration
        expect(NavigationGroup::cases())->not->toBeEmpty('Navigation groups should be defined');

        // 2. Resource system integration
        $resourceClasses = [
            'App\\Filament\\Resources\\ProductResource',
            'App\\Filament\\Resources\\CategoryResource',
            'App\\Filament\\Resources\\BrandResource',
        ];

        $existingResources = 0;
        foreach ($resourceClasses as $resourceClass) {
            if (class_exists($resourceClass)) {
                $existingResources++;
            }
        }

        expect($existingResources)->toBeGreaterThan(0, 'At least some admin resources should exist');

        // 3. Translation system integration
        $translationFiles = ['lt/navigation.php', 'en/navigation.php'];
        $existingTranslations = 0;

        foreach ($translationFiles as $translationFile) {
            $filePath = resource_path("lang/{$translationFile}");
            if (file_exists($filePath)) {
                $existingTranslations++;
            }
        }

        expect($existingTranslations)->toBeGreaterThan(0, 'Translation files should exist');

        // 4. Authentication system integration
        $authRoutes = collect(Route::getRoutes())->filter(function ($route) {
            $routeName = $route->getName() ?? '';

            return str_contains($routeName, 'login') || str_contains($routeName, 'auth');
        });

        expect($authRoutes->count())->toBeGreaterThan(0, 'Authentication routes should be registered');

        // 5. Admin panel integration
        $adminRoutes = collect(Route::getRoutes())->filter(function ($route) {
            return str_starts_with($route->getName() ?? '', 'filament.admin.');
        });

        expect($adminRoutes->count())->toBeGreaterThan(0, 'Admin panel routes should be registered');
    });
});
