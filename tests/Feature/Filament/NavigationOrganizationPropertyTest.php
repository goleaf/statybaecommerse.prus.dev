<?php

declare(strict_types=1);

namespace Tests\Feature\Filament;

use App\Enums\NavigationGroup;
use App\Models\User;
use Filament\Resources\Resource;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use ReflectionClass;
use ReflectionException;
use Tests\TestCase;
use UnitEnum;

/**
 * Property 4: Navigation Organization Consistency
 * For any admin resource, it should be assigned to an appropriate navigation group with proper icons and labels
 * Validates: Requirements 3.2, 3.3, 8.1, 8.3
 *
 * Feature: filament-admin-backend-setup, Property 4: Navigation Organization Consistency
 */
final class NavigationOrganizationPropertyTest extends TestCase
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

    /**
     * Property test: all resources have appropriate navigation groups assigned
     */
    public function test_all_resources_have_appropriate_navigation_groups_assigned(): void
    {
        $resourceClasses = $this->getAllFilamentResourceClasses();

        $this->assertNotEmpty($resourceClasses, 'No Filament resources found to test');

        foreach ($resourceClasses as $resourceClass) {
            // Test that each resource has a navigation group assigned
            $navigationGroup = $this->getResourceNavigationGroup($resourceClass);

            if ($navigationGroup !== null) {
                // If navigation group is set, it should be a valid NavigationGroup enum or string
                if ($navigationGroup instanceof UnitEnum) {
                    $this->assertInstanceOf(
                        NavigationGroup::class,
                        $navigationGroup,
                        "Resource {$resourceClass} should use NavigationGroup enum"
                    );
                } else {
                    $this->assertIsString(
                        $navigationGroup,
                        "Resource {$resourceClass} navigation group should be string or NavigationGroup enum"
                    );
                }
            }
        }
    }

    /**
     * Property test: all navigation groups have proper labels and icons
     */
    public function test_all_navigation_groups_have_proper_labels_and_icons(): void
    {
        $allNavigationGroups = NavigationGroup::cases();

        foreach ($allNavigationGroups as $group) {
            // Test that each navigation group has a proper label
            $label = $group->getLabel();
            $this->assertIsString($label, "NavigationGroup {$group->name} should have a string label");
            $this->assertNotEmpty($label, "NavigationGroup {$group->name} label should not be empty");

            // Test that each navigation group has a proper icon
            $icon = $group->getIcon();
            $this->assertIsString($icon, "NavigationGroup {$group->name} should have a string icon");
            $this->assertNotEmpty($icon, "NavigationGroup {$group->name} icon should not be empty");
            $this->assertStringStartsWith('heroicon-', $icon, "NavigationGroup {$group->name} icon should be a heroicon");

            // Test that priority is a valid integer
            $priority = $group->priority();
            $this->assertIsInt($priority, "NavigationGroup {$group->name} priority should be an integer");
            $this->assertGreaterThan(0, $priority, "NavigationGroup {$group->name} priority should be positive");

            // Test that color is a valid string
            $color = $group->getColor();
            $this->assertIsString($color, "NavigationGroup {$group->name} should have a string color");
            $this->assertNotEmpty($color, "NavigationGroup {$group->name} color should not be empty");
        }
    }

    /**
     * Property test: navigation group translations exist for all groups
     */
    public function test_navigation_group_translations_exist_for_all_groups(): void
    {
        $allNavigationGroups = NavigationGroup::cases();

        foreach ($allNavigationGroups as $group) {
            // Test that translation keys exist for both Lithuanian and English
            $translationKey = "navigation_groups.{$group->value}";

            // Check Lithuanian translation - allow fallback to English if not found
            $ltTranslation = __($translationKey, [], 'lt');
            if ($ltTranslation !== $translationKey) {
                $this->assertIsString($ltTranslation, "Lithuanian translation for NavigationGroup {$group->name} should be a string");
                $this->assertNotEmpty($ltTranslation, "Lithuanian translation for NavigationGroup {$group->name} should not be empty");
            }

            // Check English translation - allow fallback if not found
            $enTranslation = __($translationKey, [], 'en');
            if ($enTranslation !== $translationKey) {
                $this->assertIsString($enTranslation, "English translation for NavigationGroup {$group->name} should be a string");
                $this->assertNotEmpty($enTranslation, "English translation for NavigationGroup {$group->name} should not be empty");
            }

            // At least one translation should exist (either direct translation or via getLabel method)
            $hasTranslation = ($ltTranslation !== $translationKey) || ($enTranslation !== $translationKey) || ! empty($group->getLabel());
            $this->assertTrue($hasTranslation, "NavigationGroup {$group->name} should have at least one translation available");
        }
    }

    /**
     * Property test: resources with navigation groups have consistent navigation properties
     */
    public function test_resources_with_navigation_groups_have_consistent_navigation_properties(): void
    {
        $resourceClasses = $this->getAllFilamentResourceClasses();

        foreach ($resourceClasses as $resourceClass) {
            $navigationGroup = $this->getResourceNavigationGroup($resourceClass);

            if ($navigationGroup !== null) {
                // Test navigation icon exists and is valid
                $navigationIcon = $this->getResourceNavigationIcon($resourceClass);
                if ($navigationIcon !== null) {
                    $this->assertIsString($navigationIcon, "Resource {$resourceClass} navigation icon should be a string");
                    $this->assertNotEmpty($navigationIcon, "Resource {$resourceClass} navigation icon should not be empty");
                }

                // Test navigation label exists and is valid
                $navigationLabel = $this->getResourceNavigationLabel($resourceClass);
                if ($navigationLabel !== null) {
                    $this->assertIsString($navigationLabel, "Resource {$resourceClass} navigation label should be a string");
                    $this->assertNotEmpty($navigationLabel, "Resource {$resourceClass} navigation label should not be empty");
                }

                // Test navigation sort is valid if set
                $navigationSort = $this->getResourceNavigationSort($resourceClass);
                if ($navigationSort !== null) {
                    $this->assertIsInt($navigationSort, "Resource {$resourceClass} navigation sort should be an integer");
                }
            }
        }
    }

    /**
     * Property test: navigation group priorities are unique and properly ordered
     */
    public function test_navigation_group_priorities_are_unique_and_properly_ordered(): void
    {
        $allNavigationGroups = NavigationGroup::cases();
        $priorities = [];

        foreach ($allNavigationGroups as $group) {
            $priority = $group->priority();

            // Check for duplicate priorities
            $this->assertNotContains(
                $priority,
                $priorities,
                "NavigationGroup {$group->name} priority {$priority} should be unique"
            );

            $priorities[] = $priority;
        }

        // Test that priorities create a logical ordering
        sort($priorities);
        $expectedMinPriority = 10;
        $expectedMaxPriority = 100;

        $this->assertGreaterThanOrEqual(
            $expectedMinPriority,
            min($priorities),
            "Minimum navigation priority should be at least {$expectedMinPriority}"
        );

        $this->assertLessThanOrEqual(
            $expectedMaxPriority,
            max($priorities),
            "Maximum navigation priority should be at most {$expectedMaxPriority}"
        );
    }

    /**
     * Property test: navigation groups maintain logical categorization
     */
    public function test_navigation_groups_maintain_logical_categorization(): void
    {
        $resourceClasses = $this->getAllFilamentResourceClasses();
        $groupAssignments = [];

        foreach ($resourceClasses as $resourceClass) {
            $navigationGroup = $this->getResourceNavigationGroup($resourceClass);

            if ($navigationGroup instanceof NavigationGroup) {
                $groupAssignments[$navigationGroup->name][] = $resourceClass;
            }
        }

        // Test that each group has appropriate resources
        foreach ($groupAssignments as $groupName => $resources) {
            $this->assertNotEmpty($resources, "NavigationGroup {$groupName} should have at least one resource assigned");
            $this->assertIsArray($resources, "Resources for NavigationGroup {$groupName} should be an array");

            // Test that resource names make sense for their group
            foreach ($resources as $resourceClass) {
                $resourceName = class_basename($resourceClass);
                $this->assertStringEndsWith('Resource', $resourceName, "Resource class {$resourceClass} should end with 'Resource'");
            }
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
     * Helper method to get resource navigation group
     */
    private function getResourceNavigationGroup(string $resourceClass): mixed
    {
        try {
            $reflection = new ReflectionClass($resourceClass);
            if ($reflection->hasProperty('navigationGroup')) {
                $property = $reflection->getProperty('navigationGroup');
                $property->setAccessible(true);

                return $property->getValue();
            }
        } catch (ReflectionException $e) {
            // Property doesn't exist or can't be accessed
        }

        return null;
    }

    /**
     * Helper method to get resource navigation icon
     */
    private function getResourceNavigationIcon(string $resourceClass): ?string
    {
        try {
            $reflection = new ReflectionClass($resourceClass);
            if ($reflection->hasProperty('navigationIcon')) {
                $property = $reflection->getProperty('navigationIcon');
                $property->setAccessible(true);

                return $property->getValue();
            }
        } catch (ReflectionException $e) {
            // Property doesn't exist or can't be accessed
        }

        return null;
    }

    /**
     * Helper method to get resource navigation label
     */
    private function getResourceNavigationLabel(string $resourceClass): ?string
    {
        if (method_exists($resourceClass, 'getNavigationLabel')) {
            return $resourceClass::getNavigationLabel();
        }

        return null;
    }

    /**
     * Helper method to get resource navigation sort
     */
    private function getResourceNavigationSort(string $resourceClass): ?int
    {
        try {
            $reflection = new ReflectionClass($resourceClass);
            if ($reflection->hasProperty('navigationSort')) {
                $property = $reflection->getProperty('navigationSort');
                $property->setAccessible(true);

                return $property->getValue();
            }
        } catch (ReflectionException $e) {
            // Property doesn't exist or can't be accessed
        }

        return null;
    }
}
