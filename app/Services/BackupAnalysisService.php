<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

/**
 * Service to analyze backup files and create restoration plan
 */
class BackupAnalysisService
{
    private string $backupPath;

    public function __construct(string $backupPath = 'backups/pre-downgrade-20260105-163000')
    {
        $this->backupPath = base_path($backupPath);
    }

    /**
     * Analyze backup and create restoration inventory
     */
    public function analyzeBackup(): array
    {
        $analysis = [
            'resources'            => $this->catalogResources(),
            'pages'                => $this->catalogPages(),
            'widgets'              => $this->catalogWidgets(),
            'components'           => $this->catalogComponents(),
            'admin_panel_provider' => $this->hasAdminPanelProvider(),
            'restoration_order'    => $this->createRestorationOrder(),
        ];

        return $analysis;
    }

    /**
     * Catalog all Filament resources
     */
    public function catalogResources(): Collection
    {
        $resourcesPath = $this->backupPath . '/filament-backup/Resources';

        if (! File::exists($resourcesPath)) {
            return collect();
        }

        $resources = collect();
        $files = File::allFiles($resourcesPath);

        foreach ($files as $file) {
            if ($file->getExtension() === 'php' && ! Str::contains($file->getPath(), '/Pages/')) {
                $relativePath = str_replace($resourcesPath . '/', '', $file->getPathname());
                $className = $this->extractClassName($file->getContents());

                $resources->push([
                    'file'      => $relativePath,
                    'class'     => $className,
                    'category'  => $this->categorizeResource($className),
                    'priority'  => $this->getResourcePriority($className),
                    'full_path' => $file->getPathname(),
                ]);
            }
        }

        return $resources->sortBy('priority');
    }

    /**
     * Catalog Filament pages
     */
    public function catalogPages(): Collection
    {
        $pagesPath = $this->backupPath . '/filament-backup/Pages';

        if (! File::exists($pagesPath)) {
            return collect();
        }

        $pages = collect();
        $files = File::allFiles($pagesPath);

        foreach ($files as $file) {
            if ($file->getExtension() === 'php') {
                $relativePath = str_replace($pagesPath . '/', '', $file->getPathname());
                $className = $this->extractClassName($file->getContents());

                $pages->push([
                    'file'      => $relativePath,
                    'class'     => $className,
                    'full_path' => $file->getPathname(),
                ]);
            }
        }

        return $pages;
    }

    /**
     * Catalog widgets
     */
    public function catalogWidgets(): Collection
    {
        $widgetsPath = $this->backupPath . '/filament-backup/Widgets';

        if (! File::exists($widgetsPath)) {
            return collect();
        }

        $widgets = collect();
        $files = File::allFiles($widgetsPath);

        foreach ($files as $file) {
            if ($file->getExtension() === 'php') {
                $relativePath = str_replace($widgetsPath . '/', '', $file->getPathname());
                $className = $this->extractClassName($file->getContents());

                $widgets->push([
                    'file'      => $relativePath,
                    'class'     => $className,
                    'full_path' => $file->getPathname(),
                ]);
            }
        }

        return $widgets;
    }

    /**
     * Catalog custom components
     */
    public function catalogComponents(): Collection
    {
        $componentsPath = $this->backupPath . '/filament-backup/Components';

        if (! File::exists($componentsPath)) {
            return collect();
        }

        $components = collect();
        $files = File::allFiles($componentsPath);

        foreach ($files as $file) {
            if ($file->getExtension() === 'php') {
                $relativePath = str_replace($componentsPath . '/', '', $file->getPathname());
                $className = $this->extractClassName($file->getContents());

                $components->push([
                    'file'      => $relativePath,
                    'class'     => $className,
                    'full_path' => $file->getPathname(),
                ]);
            }
        }

        return $components;
    }

    /**
     * Check if AdminPanelProvider exists
     */
    public function hasAdminPanelProvider(): bool
    {
        return File::exists($this->backupPath . '/filament-backup/AdminPanelProvider.php');
    }

    /**
     * Create restoration order based on dependencies
     */
    public function createRestorationOrder(): array
    {
        return [
            'phase_1_core' => [
                'ProductResource',
                'CategoryResource',
                'BrandResource',
                'CustomerResource',
                'UserResource',
            ],
            'phase_2_inventory' => [
                'InventoryResource',
                'PriceResource',
                'DiscountResource',
                'StockMovementResource',
            ],
            'phase_3_orders' => [
                'OrderResource',
                'OrderItemResource',
                'OrderShippingResource',
                'CouponResource',
            ],
            'phase_4_content' => [
                'NewsResource',
                'SliderResource',
                'MenuResource',
                'LegalResource',
            ],
            'phase_5_analytics' => [
                'AnalyticsResource',
                'CampaignResource',
                'RecommendationConfigResource',
            ],
        ];
    }

    /**
     * Extract class name from file content
     */
    private function extractClassName(string $content): ?string
    {
        if (preg_match('/class\s+(\w+)/', $content, $matches)) {
            return $matches[1];
        }

        return null;
    }

    /**
     * Categorize resource by name
     */
    private function categorizeResource(string $className): string
    {
        if (Str::contains($className, ['Product', 'Category', 'Brand', 'Inventory'])) {
            return 'catalog';
        }

        if (Str::contains($className, ['Order', 'Customer', 'User', 'Coupon'])) {
            return 'commerce';
        }

        if (Str::contains($className, ['News', 'Slider', 'Menu', 'Legal'])) {
            return 'content';
        }

        if (Str::contains($className, ['Analytics', 'Campaign', 'Recommendation'])) {
            return 'analytics';
        }

        return 'system';
    }

    /**
     * Get restoration priority (lower number = higher priority)
     */
    private function getResourcePriority(string $className): int
    {
        $highPriority = ['ProductResource', 'CategoryResource', 'BrandResource', 'CustomerResource', 'UserResource'];
        $mediumPriority = ['OrderResource', 'InventoryResource', 'PriceResource'];

        if (in_array($className, $highPriority)) {
            return 1;
        }

        if (in_array($className, $mediumPriority)) {
            return 2;
        }

        return 3;
    }
}
