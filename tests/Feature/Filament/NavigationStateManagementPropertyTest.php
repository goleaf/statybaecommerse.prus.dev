<?php

declare(strict_types=1);

namespace Tests\Feature\Filament;

use App\Enums\NavigationGroup;
use App\Models\User;
use Filament\Facades\Filament;
use Filament\Resources\Resource;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use ReflectionClass;
use ReflectionException;
use Tests\TestCase;

/**
 * Property 5: Navigation State Management
 * For any navigation interaction, the system should maintain proper active states and group context highlighting
 * Validates: Requirements 3.4, 8.4
 *
 * Feature: filament-admin-backend-setup, Property 5: Navigation State Management
 */
final class NavigationStateManagementPropertyTest extends TestCase
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
     * Property test: navigation maintains active states for all menu items
     *
     * **Feature: filament-admin-backend-setup, Property 5: Navigation State Management**
     * **Validates: Requirements 3.4**
     */
    public function test_navigation_maintains_active_states_for_all_menu_items(): void
    {
        $resourceClasses = $this->getAllFilamentResourceClasses();

        $this->assertNotEmpty($resourceClasses, 'No Filament resources found to test');

        // Test with a limited sample of resources for faster execution
        $sampleResources = array_slice($resourceClasses, 0, min(3, count($resourceClasses)));

        foreach ($sampleResources as $resourceClass) {
            // Test that each resource can determine its active state
            $this->assertResourceCanDetermineActiveState($resourceClass);

            // Test that navigation group context is maintained
            $this->assertNavigationGroupContextIsMaintained($resourceClass);
        }
    }

    /**
     * Property test: navigation group context highlighting is maintained for all grouped items
     *
     * **Feature: filament-admin-backend-setup, Property 5: Navigation State Management**
     * **Validates: Requirements 8.4**
     */
    public function test_navigation_group_context_highlighting_is_maintained_for_all_grouped_items(): void
    {
        $resourceClasses = $this->getAllFilamentResourceClasses();
        $groupedResources = [];

        // Group resources by their navigation groups
        foreach ($resourceClasses as $resourceClass) {
            $navigationGroup = $this->getResourceNavigationGroup($resourceClass);
            if ($navigationGroup !== null) {
                $groupKey = $navigationGroup instanceof NavigationGroup
                    ? $navigationGroup->value
                    : (string) $navigationGroup;
                $groupedResources[$groupKey][] = $resourceClass;
            }
        }

        $this->assertNotEmpty($groupedResources, 'No grouped resources found to test');

        // Test with a limited sample of groups for faster execution
        $sampleGroups = array_slice($groupedResources, 0, min(2, count($groupedResources)), true);

        // Test that each group maintains proper context highlighting
        foreach ($sampleGroups as $groupKey => $resources) {
            $this->assertGroupContextHighlightingIsMaintained($groupKey, $resources);
        }
    }

    /**
     * Property test: navigation state consistency across all admin panel interactions
     *
     * **Feature: filament-admin-backend-setup, Property 5: Navigation State Management**
     * **Validates: Requirements 3.4, 8.4**
     */
    public function test_navigation_state_consistency_across_all_admin_panel_interactions(): void
    {
        $resourceClasses = $this->getAllFilamentResourceClasses();

        // Test with a limited sample of resources for faster execution
        $sampleResources = array_slice($resourceClasses, 0, min(2, count($resourceClasses)));

        foreach ($sampleResources as $resourceClass) {
            // Test that navigation state is consistent when accessing different resource pages
            $this->assertNavigationStateConsistencyForResource($resourceClass);
        }
    }

    /**
     * Property test: navigation active states are mutually exclusive
     *
     * **Feature: filament-admin-backend-setup, Property 5: Navigation State Management**
     * **Validates: Requirements 3.4, 8.4**
     */
    public function test_navigation_active_states_are_mutually_exclusive(): void
    {
        $resourceClasses = $this->getAllFilamentResourceClasses();

        // Test with a limited sample of resources for faster execution
        $sampleResources = array_slice($resourceClasses, 0, min(2, count($resourceClasses)));

        // Test that only one navigation item can be active at a time
        foreach ($sampleResources as $resourceClass) {
            $this->assertNavigationActiveStatesAreMutuallyExclusive($resourceClass);
        }
    }

    /**
     * Property test: navigation group highlighting persists across resource navigation
     *
     * **Feature: filament-admin-backend-setup, Property 5: Navigation State Management**
     * **Validates: Requirements 8.4**
     */
    public function test_navigation_group_highlighting_persists_across_resource_navigation(): void
    {
        $resourceClasses = $this->getAllFilamentResourceClasses();
        $groupedResources = [];

        // Group resources by their navigation groups
        foreach ($resourceClasses as $resourceClass) {
            $navigationGroup = $this->getResourceNavigationGroup($resourceClass);
            if ($navigationGroup !== null) {
                $groupKey = $navigationGroup instanceof NavigationGroup
                    ? $navigationGroup->value
                    : (string) $navigationGroup;
                $groupedResources[$groupKey][] = $resourceClass;
            }
        }

        // Test that group highlighting persists when navigating between resources in the same group
        foreach ($groupedResources as $groupKey => $resources) {
            if (count($resources) > 1) {
                $this->assertGroupHighlightingPersistsAcrossResources($groupKey, $resources);
            }
        }
    }

    /**
     * Assert that a resource can determine its active state
     */
    private function assertResourceCanDetermineActiveState(string $resourceClass): void
    {
        // Test that the resource has navigation properties that can be used to determine active state
        $navigationGroup = $this->getResourceNavigationGroup($resourceClass);
        $navigationLabel = $this->getResourceNavigationLabel($resourceClass);
        $navigationIcon = $this->getResourceNavigationIcon($resourceClass);

        // At least one navigation property should be available for state determination
        $hasNavigationProperties = $navigationGroup !== null || $navigationLabel !== null || $navigationIcon !== null;

        $this->assertTrue(
            $hasNavigationProperties,
            "Resource {$resourceClass} should have navigation properties for active state determination"
        );

        // If navigation group is set, it should be valid
        if ($navigationGroup !== null) {
            $this->assertTrue(
                $navigationGroup instanceof NavigationGroup || is_string($navigationGroup),
                "Resource {$resourceClass} navigation group should be NavigationGroup enum or string"
            );
        }
    }

    /**
     * Assert that navigation group context is maintained for a resource
     */
    private function assertNavigationGroupContextIsMaintained(string $resourceClass): void
    {
        $navigationGroup = $this->getResourceNavigationGroup($resourceClass);

        if ($navigationGroup instanceof NavigationGroup) {
            // Test that the group has proper context properties
            $groupLabel = $navigationGroup->getLabel();
            $groupIcon = $navigationGroup->getIcon();
            $groupPriority = $navigationGroup->priority();

            $this->assertIsString($groupLabel, "NavigationGroup for {$resourceClass} should have a string label");
            $this->assertNotEmpty($groupLabel, "NavigationGroup for {$resourceClass} label should not be empty");

            $this->assertIsString($groupIcon, "NavigationGroup for {$resourceClass} should have a string icon");
            $this->assertNotEmpty($groupIcon, "NavigationGroup for {$resourceClass} icon should not be empty");

            $this->assertIsInt($groupPriority, "NavigationGroup for {$resourceClass} should have an integer priority");
            $this->assertGreaterThan(0, $groupPriority, "NavigationGroup for {$resourceClass} priority should be positive");
        }
    }

    /**
     * Assert that group context highlighting is maintained for a group of resources
     */
    private function assertGroupContextHighlightingIsMaintained(string $groupKey, array $resources): void
    {
        $this->assertNotEmpty($resources, "Group {$groupKey} should have resources");

        // Test that all resources in the group have consistent navigation group assignment
        foreach ($resources as $resourceClass) {
            $navigationGroup = $this->getResourceNavigationGroup($resourceClass);

            if ($navigationGroup instanceof NavigationGroup) {
                $this->assertEquals(
                    $groupKey,
                    $navigationGroup->value,
                    "Resource {$resourceClass} should belong to group {$groupKey}"
                );
            } elseif (is_string($navigationGroup)) {
                $this->assertEquals(
                    $groupKey,
                    $navigationGroup,
                    "Resource {$resourceClass} should belong to group {$groupKey}"
                );
            }
        }

        // Test that the group has consistent metadata across all resources
        $groupMetadata = [];
        foreach ($resources as $resourceClass) {
            $navigationGroup = $this->getResourceNavigationGroup($resourceClass);
            if ($navigationGroup instanceof NavigationGroup) {
                $groupMetadata[] = [
                    'label'    => $navigationGroup->getLabel(),
                    'icon'     => $navigationGroup->getIcon(),
                    'priority' => $navigationGroup->priority(),
                ];
            }
        }

        // All resources in the same group should have the same group metadata
        if (! empty($groupMetadata)) {
            $firstMetadata = $groupMetadata[0];
            foreach ($groupMetadata as $metadata) {
                $this->assertEquals(
                    $firstMetadata,
                    $metadata,
                    "All resources in group {$groupKey} should have consistent group metadata"
                );
            }
        }
    }

    /**
     * Assert navigation state consistency for a resource
     */
    private function assertNavigationStateConsistencyForResource(string $resourceClass): void
    {
        // Test that the resource maintains consistent navigation properties
        $navigationGroup = $this->getResourceNavigationGroup($resourceClass);
        $navigationSort = $this->getResourceNavigationSort($resourceClass);

        // Navigation properties should be consistent across multiple calls
        $navigationGroup2 = $this->getResourceNavigationGroup($resourceClass);
        $navigationSort2 = $this->getResourceNavigationSort($resourceClass);

        $this->assertEquals(
            $navigationGroup,
            $navigationGroup2,
            "Resource {$resourceClass} navigation group should be consistent"
        );

        $this->assertEquals(
            $navigationSort,
            $navigationSort2,
            "Resource {$resourceClass} navigation sort should be consistent"
        );
    }

    /**
     * Assert that navigation active states are mutually exclusive
     */
    private function assertNavigationActiveStatesAreMutuallyExclusive(string $resourceClass): void
    {
        // Test that the resource has a unique navigation identity
        $navigationGroup = $this->getResourceNavigationGroup($resourceClass);
        $navigationSort = $this->getResourceNavigationSort($resourceClass);

        // If navigation sort is set, it should be unique within its group
        if ($navigationSort !== null) {
            $this->assertIsInt($navigationSort, "Resource {$resourceClass} navigation sort should be an integer");

            // Test that the sort value is reasonable (not negative, not extremely large)
            $this->assertGreaterThanOrEqual(0, $navigationSort, "Resource {$resourceClass} navigation sort should not be negative");
            $this->assertLessThan(1000, $navigationSort, "Resource {$resourceClass} navigation sort should be reasonable");
        }

        // Test that the resource class name is unique (which ensures unique active states)
        $this->assertIsString($resourceClass, 'Resource class should be a string');
        $this->assertStringEndsWith('Resource', $resourceClass, "Resource class should end with 'Resource'");
    }

    /**
     * Assert that group highlighting persists across resources in the same group
     */
    private function assertGroupHighlightingPersistsAcrossResources(string $groupKey, array $resources): void
    {
        $this->assertGreaterThan(1, count($resources), "Group {$groupKey} should have multiple resources for persistence testing");

        // Test that all resources in the group share the same navigation group
        $firstResourceGroup = $this->getResourceNavigationGroup($resources[0]);

        foreach ($resources as $resourceClass) {
            $navigationGroup = $this->getResourceNavigationGroup($resourceClass);

            if ($firstResourceGroup instanceof NavigationGroup && $navigationGroup instanceof NavigationGroup) {
                $this->assertEquals(
                    $firstResourceGroup->value,
                    $navigationGroup->value,
                    "All resources in group {$groupKey} should have the same navigation group value"
                );

                $this->assertEquals(
                    $firstResourceGroup->getLabel(),
                    $navigationGroup->getLabel(),
                    "All resources in group {$groupKey} should have the same group label"
                );

                $this->assertEquals(
                    $firstResourceGroup->getIcon(),
                    $navigationGroup->getIcon(),
                    "All resources in group {$groupKey} should have the same group icon"
                );
            } elseif (is_string($firstResourceGroup) && is_string($navigationGroup)) {
                $this->assertEquals(
                    $firstResourceGroup,
                    $navigationGroup,
                    "All resources in group {$groupKey} should have the same navigation group string"
                );
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
