<?php declare(strict_types=1);

namespace App\Support\Testing;

use Illuminate\Testing\TestView;

final class SharedComponentTestHelper
{
    public static function assertButtonHasVariant(TestView $view, string $variant): void
    {
        $variantClasses = match($variant) {
            'primary' => 'bg-blue-600',
            'secondary' => 'bg-gray-200',
            'danger' => 'bg-red-600',
            'success' => 'bg-green-600',
            'warning' => 'bg-yellow-600',
            'ghost' => 'hover:bg-gray-100',
            default => 'bg-blue-600',
        };

        $view->assertSee($variantClasses, false);
    }

    public static function assertButtonHasSize(TestView $view, string $size): void
    {
        $sizeClasses = match($size) {
            'sm' => 'px-3 py-2 text-sm',
            'md' => 'px-4 py-2 text-sm',
            'lg' => 'px-6 py-3 text-base',
            'xl' => 'px-8 py-4 text-lg',
            default => 'px-4 py-2 text-sm',
        };

        $view->assertSee($sizeClasses, false);
    }

    public static function assertCardHasSlots(TestView $view, array $slots): void
    {
        foreach ($slots as $slot) {
            $view->assertSee($slot);
        }
    }

    public static function assertBadgeHasVariant(TestView $view, string $variant): void
    {
        $variantClasses = match($variant) {
            'primary' => 'bg-blue-100 text-blue-800',
            'secondary' => 'bg-gray-100 text-gray-800',
            'success' => 'bg-green-100 text-green-800',
            'warning' => 'bg-yellow-100 text-yellow-800',
            'danger' => 'bg-red-100 text-red-800',
            'info' => 'bg-purple-100 text-purple-800',
            'gray' => 'bg-gray-100 text-gray-600',
            default => 'bg-blue-100 text-blue-800',
        };

        $view->assertSee($variantClasses, false);
    }

    public static function assertInputHasValidation(TestView $view, bool $hasError = false): void
    {
        if ($hasError) {
            $view->assertSee('border-red-500', false);
            $view->assertSee('text-red-600', false);
        } else {
            $view->assertSee('border-gray-300', false);
        }
    }

    public static function assertProductCardHasActions(TestView $view, array $actions): void
    {
        $actionMap = [
            'wishlist' => 'toggleWishlist',
            'compare' => 'addToCompare',
            'cart' => 'addToCart',
        ];

        foreach ($actions as $action) {
            if (isset($actionMap[$action])) {
                $view->assertSee($actionMap[$action], false);
            }
        }
    }

    public static function assertEmptyStateHasAction(TestView $view, string $actionType): void
    {
        $actionElements = [
            'url' => 'href=',
            'wire' => 'wire:click=',
        ];

        if (isset($actionElements[$actionType])) {
            $view->assertSee($actionElements[$actionType], false);
        }
    }

    public static function assertPaginationWorks(TestView $view, bool $hasPages = true): void
    {
        if ($hasPages) {
            $view->assertSee('Previous');
            $view->assertSee('Next');
            $view->assertSee('Showing');
        } else {
            $view->assertDontSee('Previous');
            $view->assertDontSee('Next');
        }
    }

    public static function assertModalIsConfigured(TestView $view, array $config): void
    {
        if ($config['closeable'] ?? true) {
            $view->assertSee('x-show="show"', false);
        }

        if (isset($config['title'])) {
            $view->assertSee($config['title']);
        }

        $maxWidth = $config['maxWidth'] ?? 'md';
        $widthClasses = match($maxWidth) {
            'sm' => 'sm:max-w-sm',
            'md' => 'sm:max-w-md',
            'lg' => 'sm:max-w-lg',
            'xl' => 'sm:max-w-xl',
            '2xl' => 'sm:max-w-2xl',
            default => 'sm:max-w-md',
        };

        $view->assertSee($widthClasses, false);
    }

    public static function assertNotificationHasType(TestView $view, string $type): void
    {
        $typeClasses = match($type) {
            'success' => 'bg-green-50 border-green-200 text-green-800',
            'error' => 'bg-red-50 border-red-200 text-red-800',
            'warning' => 'bg-yellow-50 border-yellow-200 text-yellow-800',
            'info' => 'bg-blue-50 border-blue-200 text-blue-800',
            default => 'bg-blue-50 border-blue-200 text-blue-800',
        };

        $view->assertSee($typeClasses, false);
    }

    public static function assertSearchBarHasFeatures(TestView $view, array $features): void
    {
        if (in_array('suggestions', $features)) {
            $view->assertSee('x-show="showSuggestions"', false);
        }

        if (in_array('advanced', $features)) {
            $view->assertSee('showAdvanced', false);
        }

        if (in_array('filters', $features)) {
            $view->assertSee('toggle-filters', false);
        }
    }

    public static function assertLoadingHasType(TestView $view, string $type): void
    {
        $typeElements = [
            'spinner' => 'animate-spin',
            'skeleton' => 'animate-pulse',
            'pulse' => 'animate-pulse',
            'dots' => 'animate-bounce',
        ];

        if (isset($typeElements[$type])) {
            $view->assertSee($typeElements[$type], false);
        }
    }

    public static function assertComponentAccessibility(TestView $view): void
    {
        // Check for ARIA labels
        $view->assertSeeInOrder(['aria-label', 'role'], false);
        
        // Check for proper heading structure
        $view->assertSee(['<h1', '<h2', '<h3'], false);
        
        // Check for alt text on images
        $view->assertSee('alt=', false);
    }

    public static function assertResponsiveDesign(TestView $view): void
    {
        // Check for responsive classes
        $responsiveClasses = [
            'sm:', 'md:', 'lg:', 'xl:', '2xl:',
            'grid-cols-1', 'sm:grid-cols-2', 'lg:grid-cols-3'
        ];

        foreach ($responsiveClasses as $class) {
            $view->assertSee($class, false);
        }
    }

    public static function assertDarkModeSupport(TestView $view): void
    {
        // Check for dark mode classes
        $darkClasses = [
            'dark:bg-', 'dark:text-', 'dark:border-', 'dark:hover:'
        ];

        $hasDarkMode = false;
        foreach ($darkClasses as $class) {
            if ($view->content && str_contains($view->content, $class)) {
                $hasDarkMode = true;
                break;
            }
        }

        if (!$hasDarkMode) {
            throw new \Exception('Component does not support dark mode');
        }
    }
}
