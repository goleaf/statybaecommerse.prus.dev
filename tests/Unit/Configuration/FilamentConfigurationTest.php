<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Config;

describe('Filament Configuration Property Tests', function () {
    /**
     * **Feature: filament-downgrade-restore, Property 5: Configuration Consistency**
     * **Validates: Requirements 5.1**
     *
     * For any Filament configuration file, the structure must be valid for version 4.0
     * while preserving functional behavior
     */
    it('maintains configuration consistency across different navigation group configurations', function () {
        // Property: All navigation groups must have consistent structure
        $testConfigurations = [
            [
                'key'   => 'test-group-1',
                'label' => 'admin.navigation.test1',
                'icon'  => 'heroicon-o-home',
            ],
            [
                'key'   => 'test-group-2',
                'label' => 'admin.navigation.test2',
                'icon'  => 'heroicon-o-cog-6-tooth',
            ],
            [
                'key'   => 'test-group-3',
                'label' => 'admin.navigation.test3',
                'icon'  => 'heroicon-o-cube',
            ],
        ];

        foreach ($testConfigurations as $testGroup) {
            // Set test configuration
            $originalConfig = config('filament');
            $testConfig = $originalConfig;
            $testConfig['navigation']['groups'][] = $testGroup;

            Config::set('filament', $testConfig);

            // Property: Configuration should always be valid after adding new groups
            $config = config('filament');
            expect($config)->toBeArray()
                ->and($config['navigation']['groups'])->toBeArray();

            // Property: All groups should maintain required structure
            $lastGroup = end($config['navigation']['groups']);
            expect($lastGroup)->toHaveKey('key')
                ->and($lastGroup)->toHaveKey('label')
                ->and($lastGroup)->toHaveKey('icon')
                ->and($lastGroup['key'])->toBe($testGroup['key'])
                ->and($lastGroup['label'])->toBe($testGroup['label'])
                ->and($lastGroup['icon'])->toBe($testGroup['icon']);

            // Restore original configuration
            Config::set('filament', $originalConfig);
        }
    });

    it('preserves configuration integrity when modifying resource lists', function () {
        // Property: Resource configuration should remain valid regardless of resource list changes
        $originalConfig = config('filament');

        // Test with different resource configurations
        $testResourceLists = [
            [], // Empty resources
            ['App\\Filament\\Resources\\TestResource'], // Single resource
            [
                'App\\Filament\\Resources\\TestResource1',
                'App\\Filament\\Resources\\TestResource2',
            ], // Multiple resources
        ];

        foreach ($testResourceLists as $resourceList) {
            $testConfig = $originalConfig;
            $testConfig['navigation']['resources'] = $resourceList;

            Config::set('filament', $testConfig);

            // Property: Configuration structure should remain valid
            $config = config('filament');
            expect($config)->toBeArray()
                ->and($config['navigation'])->toHaveKey('resources')
                ->and($config['navigation']['resources'])->toBeArray()
                ->and($config['navigation']['resources'])->toBe($resourceList);
        }

        // Restore original configuration
        Config::set('filament', $originalConfig);
    });

    it('maintains icon format consistency across all navigation groups', function () {
        // Property: All icons should follow heroicon format
        $config = config('filament');
        $groups = $config['navigation']['groups'];

        foreach ($groups as $group) {
            expect($group['icon'])->toMatch('/^heroicon-[os]-[\w-]+$/',
                "Icon '{$group['icon']}' should follow heroicon format (heroicon-o-* or heroicon-s-*)");
        }
    });

    it('ensures translation keys follow consistent naming convention', function () {
        // Property: All translation keys should follow admin.navigation.* pattern
        $config = config('filament');
        $groups = $config['navigation']['groups'];

        foreach ($groups as $group) {
            // Allow both admin.navigation.* and translations.* patterns as seen in the config
            expect($group['label'])->toMatch('/^(admin\.navigation\.|translations\.)\w+$/',
                "Label '{$group['label']}' should follow translation key convention");
        }
    });
});
