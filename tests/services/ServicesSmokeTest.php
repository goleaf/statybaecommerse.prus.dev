<?php

declare(strict_types=1);

use Tests\TestCase;

uses(TestCase::class);

/**
 * Consolidated smoke tests for all services under app/Services/**.
 * - Verifies classes autoload
 * - Attempts container instantiation if instantiable
 */
$serviceClasses = [
    // Root Services
    App\Services\XmlCatalogService::class,
    App\Services\TranslationService::class,
    App\Services\TimeoutService::class,
    App\Services\SystemSettingsService::class,
    App\Services\SEOService::class,
    App\Services\SearchService::class,
    App\Services\SearchRecommendationsService::class,
    App\Services\SearchRankingService::class,
    App\Services\SearchPerformanceService::class,
    App\Services\SearchPaginationService::class,
    App\Services\SearchInsightsService::class,
    App\Services\SearchHighlightingService::class,
    App\Services\SearchExportService::class,
    App\Services\SearchCacheService::class,
    App\Services\ReportGenerationService::class,
    App\Services\ReferralService::class,
    App\Services\ReferralRewardService::class,
    App\Services\RecommendationService::class,
    App\Services\ProductGalleryService::class,
    App\Services\NotificationService::class,
    App\Services\MultiLanguageTabService::class,
    App\Services\ImageConversionService::class,
    App\Services\EmailMarketingService::class,
    App\Services\PaginationService::class,
    App\Services\LiveNotificationService::class,
    App\Services\InventoryService::class,
    App\Services\EnumService::class,
    App\Services\DocumentService::class,
    App\Services\DataFilteringService::class,
    App\Services\DatabaseDateService::class,
    App\Services\CodeStyleService::class,
    App\Services\CacheService::class,
    App\Services\AutocompleteService::class,
    App\Services\SearchAnalyticsService::class,
    App\Services\ReferralCodeService::class,
    App\Services\CategoryDocsImporter::class,

    // Taxes
    App\Services\Taxes\TaxCalculator::class,

    // Import/Export
    App\Services\ImportExport\XmlProvider::class,
    App\Services\ImportExport\ProviderRegistry::class,
    App\Services\ImportExport\ProviderInterface::class, // interface

    // Recommendations
    App\Services\Recommendations\BaseRecommendation::class, // abstract
    App\Services\Recommendations\CollaborativeFilteringRecommendation::class,
    App\Services\Recommendations\PopularityRecommendation::class,
    App\Services\Recommendations\TrendingRecommendation::class,
    App\Services\Recommendations\HybridRecommendation::class,
    App\Services\Recommendations\CrossSellRecommendation::class,
    App\Services\Recommendations\ContentBasedRecommendation::class,
    App\Services\Recommendations\UpSellRecommendation::class,

    // Images
    App\Services\Images\ProductImageService::class,
    App\Services\Images\LocalImageGeneratorService::class,
    App\Services\Images\ImageStatsService::class,
    App\Services\Images\WebPConversionService::class,
    App\Services\Images\UltraFastProductImageService::class,
    App\Services\Images\GradientImageService::class,

    // Debug collectors
    App\Services\Debug\TranslationDebugCollector::class,
    App\Services\Debug\EcommerceDebugCollector::class,
    App\Services\Debug\DiscountDebugCollector::class,
    App\Services\Debug\LivewireDebugCollector::class,

    // Shared
    App\Services\Shared\ComponentRegistryService::class,
    App\Services\Shared\ComponentPerformanceService::class,
    App\Services\Shared\CacheService::class,
    App\Services\Shared\TranslationService::class,
    App\Services\Shared\ProductService::class,
    App\Services\Shared\ComponentValidationService::class,

    // Payments & Discounts
    App\Services\Payments\PaymentService::class,
    App\Services\Discounts\DiscountEngine::class,
];

it('autoloads all service classes', function () use ($serviceClasses) {
    foreach ($serviceClasses as $class) {
        $exists = class_exists($class) || interface_exists($class);
        expect($exists)
            ->toBeTrue("Failed asserting that {$class} exists (autoload)");
    }
});

it('can instantiate instantiable service classes via the container', function () use ($serviceClasses) {
    foreach ($serviceClasses as $class) {
        if (interface_exists($class)) {
            continue;
        }
        $ref = new ReflectionClass($class);
        if (! $ref->isInstantiable()) {
            continue;
        }

        // Some services are utility-like and can be resolved by the container.
        // We ensure resolution does not throw.
        $instance = app()->make($class);
        expect($instance)->toBeInstanceOf($class);
    }
});
