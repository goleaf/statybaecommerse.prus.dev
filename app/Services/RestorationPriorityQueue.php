<?php

declare(strict_types=1);

namespace App\Services;

use RuntimeException;

class RestorationPriorityQueue
{
    private array $priorityLevels = [
        'critical' => 1,
        'core'     => 2,
        'ui'       => 3,
        'optional' => 4,
    ];

    private array $queue = [];

    private array $completed = [];

    private array $failed = [];

    public function __construct(
        private BackupAnalysisService $backupAnalysisService
    ) {}

    /**
     * Build the restoration priority queue from cataloged resources
     */
    public function buildQueue(?array $catalogedResources = null): array
    {
        $catalogedResources = $catalogedResources ?? $this->backupAnalysisService->getCatalogedResources();

        if (empty($catalogedResources)) {
            throw new RuntimeException('No cataloged resources available. Run BackupAnalysisService::catalogResources() first.');
        }

        $prioritizedResources = $this->backupAnalysisService->prioritizeRestoration($catalogedResources);

        $this->queue = [
            'critical' => $this->buildCriticalQueue($prioritizedResources['critical']),
            'core'     => $this->buildCoreQueue($prioritizedResources['core']),
            'ui'       => $this->buildUiQueue($prioritizedResources['ui']),
            'optional' => $this->buildOptionalQueue($prioritizedResources['optional']),
        ];

        return $this->queue;
    }

    /**
     * Get the next item to restore based on priority and dependencies
     */
    public function getNext(): ?array
    {
        foreach ($this->priorityLevels as $level => $priority) {
            if (empty($this->queue[$level])) {
                continue;
            }

            foreach ($this->queue[$level] as $index => $item) {
                if ($this->areDependenciesSatisfied($item)) {
                    unset($this->queue[$level][$index]);

                    return $item;
                }
            }
        }

        return null;
    }

    /**
     * Mark an item as completed
     */
    public function markCompleted(array $item): void
    {
        $this->completed[] = $item;
    }

    /**
     * Mark an item as failed
     */
    public function markFailed(array $item, string $reason = ''): void
    {
        $item['failure_reason'] = $reason;
        $this->failed[] = $item;
    }

    /**
     * Get queue statistics
     */
    public function getStatistics(): array
    {
        $totalItems = 0;
        $remainingItems = 0;

        foreach ($this->queue as $level => $items) {
            $count = count($items);
            $totalItems += $count;
            $remainingItems += $count;
        }

        $totalProcessed = count($this->completed) + count($this->failed);
        $totalOriginal = $totalProcessed + $remainingItems;

        return [
            'total_original'      => $totalOriginal,
            'completed'           => count($this->completed),
            'failed'              => count($this->failed),
            'remaining'           => $remainingItems,
            'progress_percentage' => $totalOriginal > 0 ? round(($totalProcessed / $totalOriginal) * 100, 2) : 0,
            'by_priority'         => [
                'critical' => count($this->queue['critical'] ?? []),
                'core'     => count($this->queue['core'] ?? []),
                'ui'       => count($this->queue['ui'] ?? []),
                'optional' => count($this->queue['optional'] ?? []),
            ],
        ];
    }

    /**
     * Get items by priority level
     */
    public function getByPriority(string $priority): array
    {
        return $this->queue[$priority] ?? [];
    }

    /**
     * Get all completed items
     */
    public function getCompleted(): array
    {
        return $this->completed;
    }

    /**
     * Get all failed items
     */
    public function getFailed(): array
    {
        return $this->failed;
    }

    /**
     * Check if queue is empty
     */
    public function isEmpty(): bool
    {
        foreach ($this->queue as $items) {
            if (! empty($items)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Reset the queue
     */
    public function reset(): void
    {
        $this->queue = [];
        $this->completed = [];
        $this->failed = [];
    }

    /**
     * Build critical priority queue (models, migrations, config)
     */
    private function buildCriticalQueue(array $criticalResources): array
    {
        $queue = [];

        // 1. Migrations first (in chronological order)
        if (! empty($criticalResources['migrations'])) {
            foreach ($criticalResources['migrations'] as $migration) {
                $queue[] = [
                    'id'           => $this->generateItemId($migration),
                    'type'         => 'migration',
                    'name'         => $migration['name'],
                    'path'         => $migration['path'],
                    'priority'     => 'critical',
                    'dependencies' => [],
                    'metadata'     => $migration,
                ];
            }
        }

        // 2. Core models (ordered by dependencies)
        if (! empty($criticalResources['models'])) {
            $orderedModels = $this->orderModelsByDependencies($criticalResources['models']);

            foreach ($orderedModels as $model) {
                $queue[] = [
                    'id'           => $this->generateItemId($model),
                    'type'         => 'model',
                    'name'         => $model['name'],
                    'path'         => $model['path'],
                    'priority'     => 'critical',
                    'dependencies' => $this->extractModelDependencies($model),
                    'metadata'     => $model,
                ];
            }
        }

        // 3. Configuration files
        if (! empty($criticalResources['config'])) {
            foreach ($criticalResources['config'] as $config) {
                $queue[] = [
                    'id'           => $this->generateItemId($config),
                    'type'         => 'config',
                    'name'         => $config['name'],
                    'path'         => $config['path'],
                    'priority'     => 'critical',
                    'dependencies' => [],
                    'metadata'     => $config,
                ];
            }
        }

        return $queue;
    }

    /**
     * Build core priority queue (resources, actions, components)
     */
    private function buildCoreQueue(array $coreResources): array
    {
        $queue = [];

        // 1. Core Filament resources (ordered by business importance)
        if (! empty($coreResources['resources'])) {
            foreach ($coreResources['resources'] as $resource) {
                $queue[] = [
                    'id'           => $this->generateItemId($resource),
                    'type'         => 'resource',
                    'name'         => $resource['name'],
                    'path'         => $resource['path'],
                    'priority'     => 'core',
                    'dependencies' => $this->extractResourceDependencies($resource),
                    'metadata'     => $resource,
                ];
            }
        }

        // 2. Actions
        if (! empty($coreResources['actions'])) {
            foreach ($coreResources['actions'] as $action) {
                $queue[] = [
                    'id'           => $this->generateItemId($action),
                    'type'         => 'action',
                    'name'         => $action['name'],
                    'path'         => $action['path'],
                    'priority'     => 'core',
                    'dependencies' => [],
                    'metadata'     => $action,
                ];
            }
        }

        // 3. Components
        if (! empty($coreResources['components'])) {
            foreach ($coreResources['components'] as $component) {
                $queue[] = [
                    'id'           => $this->generateItemId($component),
                    'type'         => 'component',
                    'name'         => $component['name'],
                    'path'         => $component['path'],
                    'priority'     => 'core',
                    'dependencies' => [],
                    'metadata'     => $component,
                ];
            }
        }

        return $queue;
    }

    /**
     * Build UI priority queue (pages, widgets)
     */
    private function buildUiQueue(array $uiResources): array
    {
        $queue = [];

        // 1. Dashboard and management pages first
        if (! empty($uiResources['pages'])) {
            $orderedPages = $this->orderPagesByImportance($uiResources['pages']);

            foreach ($orderedPages as $page) {
                $queue[] = [
                    'id'           => $this->generateItemId($page),
                    'type'         => 'page',
                    'name'         => $page['name'],
                    'path'         => $page['path'],
                    'priority'     => 'ui',
                    'dependencies' => $this->extractPageDependencies($page),
                    'metadata'     => $page,
                ];
            }
        }

        // 2. Widgets (stats widgets first, then charts, then tables)
        if (! empty($uiResources['widgets'])) {
            $orderedWidgets = $this->orderWidgetsByImportance($uiResources['widgets']);

            foreach ($orderedWidgets as $widget) {
                $queue[] = [
                    'id'           => $this->generateItemId($widget),
                    'type'         => 'widget',
                    'name'         => $widget['name'],
                    'path'         => $widget['path'],
                    'priority'     => 'ui',
                    'dependencies' => $this->extractWidgetDependencies($widget),
                    'metadata'     => $widget,
                ];
            }
        }

        return $queue;
    }

    /**
     * Build optional priority queue (concerns, translations)
     */
    private function buildOptionalQueue(array $optionalResources): array
    {
        $queue = [];

        // 1. Concerns and traits
        if (! empty($optionalResources['concerns'])) {
            foreach ($optionalResources['concerns'] as $concern) {
                $queue[] = [
                    'id'           => $this->generateItemId($concern),
                    'type'         => 'concern',
                    'name'         => $concern['name'],
                    'path'         => $concern['path'],
                    'priority'     => 'optional',
                    'dependencies' => [],
                    'metadata'     => $concern,
                ];
            }
        }

        // 2. Translations
        if (! empty($optionalResources['translations'])) {
            foreach ($optionalResources['translations'] as $translation) {
                $queue[] = [
                    'id'           => $this->generateItemId($translation),
                    'type'         => 'translation',
                    'name'         => "{$translation['locale']}/{$translation['file']}",
                    'path'         => $translation['path'],
                    'priority'     => 'optional',
                    'dependencies' => [],
                    'metadata'     => $translation,
                ];
            }
        }

        return $queue;
    }

    /**
     * Order models by their dependencies
     */
    private function orderModelsByDependencies(array $models): array
    {
        // Core entities that other models depend on
        $coreModels = ['User', 'Country', 'Currency', 'Role'];

        // E-commerce foundation models
        $foundationModels = ['Category', 'Brand', 'Customer', 'Product'];

        // Secondary models
        $secondaryModels = ['Order', 'Inventory', 'Price', 'Discount'];

        $ordered = [];

        foreach ([$coreModels, $foundationModels, $secondaryModels] as $group) {
            foreach ($models as $model) {
                if (collect($group)->contains($model['name']) && ! collect($ordered)->contains('name', $model['name'])) {
                    $ordered[] = $model;
                }
            }
        }

        // Add remaining models
        foreach ($models as $model) {
            if (! collect($ordered)->contains('name', $model['name'])) {
                $ordered[] = $model;
            }
        }

        return $ordered;
    }

    /**
     * Order pages by importance
     */
    private function orderPagesByImportance(array $pages): array
    {
        $importance = [
            'dashboard'  => 1,
            'management' => 2,
            'analytics'  => 3,
            'general'    => 4,
        ];

        return collect($pages)
            ->sortBy(fn ($page) => $importance[$page['category']] ?? 999)
            ->values()
            ->toArray();
    }

    /**
     * Order widgets by importance
     */
    private function orderWidgetsByImportance(array $widgets): array
    {
        $importance = [
            'stats'   => 1,
            'chart'   => 2,
            'table'   => 3,
            'general' => 4,
        ];

        return collect($widgets)
            ->sortBy(fn ($widget) => $importance[$widget['widget_type']] ?? 999)
            ->values()
            ->toArray();
    }

    /**
     * Check if dependencies are satisfied for an item
     */
    private function areDependenciesSatisfied(array $item): bool
    {
        if (empty($item['dependencies'])) {
            return true;
        }

        $completedNames = collect($this->completed)->pluck('name')->toArray();

        foreach ($item['dependencies'] as $dependency) {
            if (! in_array($dependency, $completedNames)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Extract model dependencies
     */
    private function extractModelDependencies(array $model): array
    {
        $dependencies = [];

        if (! empty($model['relationships'])) {
            foreach ($model['relationships'] as $relationship) {
                if (! empty($relationship['related'])) {
                    $dependencies[] = $relationship['related'];
                }
            }
        }

        return array_unique($dependencies);
    }

    /**
     * Extract resource dependencies
     */
    private function extractResourceDependencies(array $resource): array
    {
        $dependencies = [];

        // Resource depends on its model
        if (! empty($resource['model'])) {
            $dependencies[] = $resource['model'];
        }

        return $dependencies;
    }

    /**
     * Extract page dependencies
     */
    private function extractPageDependencies(array $page): array
    {
        $dependencies = [];

        // Dashboard pages might depend on widgets
        if ($page['category'] === 'dashboard') {
            // Add widget dependencies if needed
        }

        return $dependencies;
    }

    /**
     * Extract widget dependencies
     */
    private function extractWidgetDependencies(array $widget): array
    {
        $dependencies = [];

        // Widgets might depend on models or resources
        // This would need to be extracted from the actual widget file content

        return $dependencies;
    }

    /**
     * Generate unique ID for queue item
     */
    private function generateItemId(array $item): string
    {
        return md5($item['type'] . ':' . $item['name'] . ':' . $item['path']);
    }

    /**
     * Get the full queue
     */
    public function getQueue(): array
    {
        return $this->queue;
    }

    /**
     * Get items that are ready to be processed (dependencies satisfied)
     */
    public function getReadyItems(): array
    {
        $ready = [];

        foreach ($this->priorityLevels as $level => $priority) {
            if (empty($this->queue[$level])) {
                continue;
            }

            foreach ($this->queue[$level] as $item) {
                if ($this->areDependenciesSatisfied($item)) {
                    $ready[] = $item;
                }
            }
        }

        return $ready;
    }

    /**
     * Get items that are blocked by dependencies
     */
    public function getBlockedItems(): array
    {
        $blocked = [];

        foreach ($this->priorityLevels as $level => $priority) {
            if (empty($this->queue[$level])) {
                continue;
            }

            foreach ($this->queue[$level] as $item) {
                if (! $this->areDependenciesSatisfied($item)) {
                    $blocked[] = $item;
                }
            }
        }

        return $blocked;
    }
}
