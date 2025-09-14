<?php

declare(strict_types=1);

namespace App\Services\Shared;

final /**
 * ComponentRegistryService
 * 
 * Service class containing business logic and external integrations.
 */
class ComponentRegistryService
{
    private array $componentRegistry = [];

    public function __construct()
    {
        $this->registerComponents();
    }

    public function getAvailableComponents(): array
    {
        return $this->componentRegistry;
    }

    public function getComponentsByCategory(string $category): array
    {
        return array_filter(
            $this->componentRegistry,
            fn ($component) => $component['category'] === $category
        );
    }

    public function getComponentInfo(string $name): ?array
    {
        return $this->componentRegistry[$name] ?? null;
    }

    private function registerComponents(): void
    {
        $this->componentRegistry = [
            // Basic UI Components
            'shared.button' => [
                'name' => 'Button',
                'category' => 'ui',
                'description' => 'Universal button component with variants and sizes',
                'props' => ['variant', 'size', 'href', 'icon', 'iconPosition', 'loading', 'disabled'],
                'variants' => ['primary', 'secondary', 'danger', 'success', 'warning', 'ghost'],
                'sizes' => ['sm', 'md', 'lg', 'xl'],
                'example' => '<x-shared.button variant="primary" size="md">Click Me</x-shared.button>',
            ],

            'shared.card' => [
                'name' => 'Card',
                'category' => 'layout',
                'description' => 'Flexible card container with header and footer slots',
                'props' => ['padding', 'shadow', 'rounded', 'hover', 'border'],
                'slots' => ['header', 'footer'],
                'example' => '<x-shared.card><x-slot name="header">Title</x-slot>Content</x-shared.card>',
            ],

            'shared.input' => [
                'name' => 'Input',
                'category' => 'forms',
                'description' => 'Form input with validation and icon support',
                'props' => ['type', 'label', 'placeholder', 'required', 'error', 'icon', 'size'],
                'types' => ['text', 'email', 'password', 'search', 'number', 'tel', 'url'],
                'example' => '<x-shared.input type="email" label="Email" placeholder="Enter email" />',
            ],

            'shared.select' => [
                'name' => 'Select',
                'category' => 'forms',
                'description' => 'Select dropdown with options support',
                'props' => ['label', 'placeholder', 'required', 'error', 'options', 'size'],
                'example' => '<x-shared.select label="Category" :options="$categories" />',
            ],

            'shared.badge' => [
                'name' => 'Badge',
                'category' => 'ui',
                'description' => 'Status badge with color variants',
                'props' => ['variant', 'size', 'rounded'],
                'variants' => ['primary', 'secondary', 'success', 'warning', 'danger', 'info', 'gray'],
                'example' => '<x-shared.badge variant="success">Active</x-shared.badge>',
            ],

            // Layout Components
            'shared.section' => [
                'name' => 'Section',
                'category' => 'layout',
                'description' => 'Page section with title, description and icon',
                'props' => ['title', 'description', 'icon', 'iconColor', 'titleSize', 'centered'],
                'example' => '<x-shared.section title="Products" icon="heroicon-o-cube">Content</x-shared.section>',
            ],

            'shared.page-header' => [
                'name' => 'Page Header',
                'category' => 'layout',
                'description' => 'Page header with breadcrumbs and actions',
                'props' => ['title', 'description', 'icon', 'breadcrumbs', 'centered'],
                'slots' => ['actions'],
                'example' => '<x-shared.page-header title="Products" :breadcrumbs="$breadcrumbs" />',
            ],

            'shared.breadcrumbs' => [
                'name' => 'Breadcrumbs',
                'category' => 'navigation',
                'description' => 'Navigation breadcrumbs with multiple separators',
                'props' => ['items', 'separator', 'showHome'],
                'separators' => ['chevron', 'slash', 'dot'],
                'example' => '<x-shared.breadcrumbs :items="$breadcrumbs" separator="chevron" />',
            ],

            // Interactive Components
            'shared.modal' => [
                'name' => 'Modal',
                'category' => 'interactive',
                'description' => 'Modal dialog with Alpine.js transitions',
                'props' => ['title', 'maxWidth', 'closeable', 'show'],
                'slots' => ['footer'],
                'example' => '<x-shared.modal title="Confirm Action">Are you sure?</x-shared.modal>',
            ],

            'shared.notification' => [
                'name' => 'Notification',
                'category' => 'interactive',
                'description' => 'Alert notification with dismissal',
                'props' => ['type', 'title', 'dismissible', 'icon'],
                'types' => ['success', 'error', 'warning', 'info'],
                'example' => '<x-shared.notification type="success">Operation completed!</x-shared.notification>',
            ],

            'shared.search-bar' => [
                'name' => 'Search Bar',
                'category' => 'interactive',
                'description' => 'Advanced search with suggestions and filters',
                'props' => ['placeholder', 'showAdvanced', 'categories', 'brands'],
                'example' => '<x-shared.search-bar :show-advanced="true" :categories="$categories" />',
            ],

            // E-commerce Components
            'shared.product-card' => [
                'name' => 'Product Card',
                'category' => 'ecommerce',
                'description' => 'Product display card with actions',
                'props' => ['product', 'showQuickAdd', 'showWishlist', 'showCompare', 'layout'],
                'layouts' => ['grid', 'list'],
                'example' => '<x-shared.product-card :product="$product" :show-quick-add="true" />',
            ],

            'shared.product-grid' => [
                'name' => 'Product Grid',
                'category' => 'ecommerce',
                'description' => 'Product grid layout with pagination',
                'props' => ['products', 'columns', 'showPagination', 'emptyStateTitle'],
                'example' => '<x-shared.product-grid :products="$products" :columns="4" />',
            ],

            'shared.filter-panel' => [
                'name' => 'Filter Panel',
                'category' => 'ecommerce',
                'description' => 'Advanced product filtering interface',
                'props' => ['categories', 'brands', 'showSearch', 'showPriceRange'],
                'example' => '<x-shared.filter-panel :categories="$categories" :brands="$brands" />',
            ],

            // Utility Components
            'shared.empty-state' => [
                'name' => 'Empty State',
                'category' => 'utility',
                'description' => 'Empty state display with actions',
                'props' => ['title', 'description', 'icon', 'actionText', 'actionUrl'],
                'example' => '<x-shared.empty-state title="No items" action-text="Add Items" />',
            ],

            'shared.loading' => [
                'name' => 'Loading',
                'category' => 'utility',
                'description' => 'Loading states and skeletons',
                'props' => ['type', 'size', 'text', 'overlay'],
                'types' => ['spinner', 'skeleton', 'pulse', 'dots'],
                'example' => '<x-shared.loading type="spinner" size="md" text="Loading..." />',
            ],

            'shared.pagination' => [
                'name' => 'Pagination',
                'category' => 'utility',
                'description' => 'Custom pagination with info display',
                'props' => ['paginator', 'showInfo'],
                'example' => '<x-shared.pagination :paginator="$products" :show-info="true" />',
            ],
        ];
    }

    public function generateComponentDocumentation(): string
    {
        $docs = "# Shared Components Reference\n\n";

        $categories = array_unique(array_column($this->componentRegistry, 'category'));

        foreach ($categories as $category) {
            $docs .= '## '.ucfirst($category)." Components\n\n";

            $categoryComponents = $this->getComponentsByCategory($category);

            foreach ($categoryComponents as $name => $component) {
                $docs .= "### {$component['name']}\n";
                $docs .= "{$component['description']}\n\n";
                $docs .= '**Props:** '.implode(', ', $component['props'])."\n\n";

                if (isset($component['variants'])) {
                    $docs .= '**Variants:** '.implode(', ', $component['variants'])."\n\n";
                }

                if (isset($component['sizes'])) {
                    $docs .= '**Sizes:** '.implode(', ', $component['sizes'])."\n\n";
                }

                $docs .= "**Example:**\n```blade\n{$component['example']}\n```\n\n";
            }
        }

        return $docs;
    }

    public function validateComponentUsage(string $componentName, array $props): array
    {
        $component = $this->getComponentInfo($componentName);

        if (! $component) {
            return ['valid' => false, 'errors' => ["Component '{$componentName}' not found"]];
        }

        $errors = [];
        $requiredProps = $component['required_props'] ?? [];

        foreach ($requiredProps as $prop) {
            if (! isset($props[$prop])) {
                $errors[] = "Required prop '{$prop}' is missing";
            }
        }

        if (isset($component['variants']) && isset($props['variant'])) {
            if (! in_array($props['variant'], $component['variants'])) {
                $errors[] = "Invalid variant '{$props['variant']}'. Valid options: ".implode(', ', $component['variants']);
            }
        }

        if (isset($component['sizes']) && isset($props['size'])) {
            if (! in_array($props['size'], $component['sizes'])) {
                $errors[] = "Invalid size '{$props['size']}'. Valid options: ".implode(', ', $component['sizes']);
            }
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors,
        ];
    }
}
