<?php

declare(strict_types=1);

namespace Tests\Feature;

/**
 * Test Groups Configuration
 * 
 * Centralized configuration for organizing tests by groups
 */
class TestGroups
{
    /**
     * Admin Resource Tests
     */
    public static function getAdminTests(): array
    {
        return [
            'AdminResourcesTestSuite',
            'ProductResourceTest',
            'UserResourceTest',
            'CategoryResourceTest',
            'OrderResourceTest',
            'OrderItemResourceTest',
            'ProductVariantResourceTest',
            'ProductHistoryResourceTest',
            'CustomerGroupResourceTest',
            'CustomerManagementResourceTest',
            'DiscountCodeResourceTest',
            'DiscountConditionResourceTest',
            'CollectionResourceTest',
            'PriceResourceTest',
            'CurrencyResourceTest',
            'LocationResourceTest',
            'ZoneResourceTest',
            'NewsResourceTest',
            'LegalResourceTest',
            'PostResourceTest',
            'RecommendationBlockResourceTest',
            'RecommendationConfigResourceTest',
            'SystemSettingsResourceTest',
            'SubscriberResourceTest',
            'ReviewResourceTest',
            'SeoDataResourceTest',
            'ReferralResourceTest',
            'ReferralRewardResourceTest',
            'StockResourceTest',
            'InventoryResourceTest',
            'NotificationResourceTest',
            'ReportResourceTest',
        ];
    }

    /**
     * Widget Tests
     */
    public static function getWidgetTests(): array
    {
        return [
            'WidgetTestSuite',
            'UltimateStatsWidgetTest',
            'ComprehensiveAnalyticsWidgetTest',
            'RecentActivityWidgetTest',
            'SimplifiedStatsWidgetTest',
            'SliderQuickActionsWidgetTest',
            'RecentSlidersWidgetTest',
            'DashboardOverviewWidgetTest',
            'EcommerceStatsWidgetTest',
            'ComprehensiveStatsWidgetTest',
            'AdvancedAnalyticsWidgetTest',
            'OrdersChartWidgetTest',
            'VariantPerformanceChartTest',
            'CampaignPerformanceWidgetTest',
            'RecentOrdersWidgetTest',
            'RecentReviewsWidgetTest',
            'RecentProductsWidgetTest',
        ];
    }

    /**
     * API Tests
     */
    public static function getApiTests(): array
    {
        return [
            'CollectionApiTest',
            'CollectionIntegrationTest',
            'CollectionLivewireTest',
            'ZoneTest',
            'ZoneResourceTest',
            'ZoneRegionCityTest',
            'ZoneManagementTest',
        ];
    }

    /**
     * Integration Tests
     */
    public static function getIntegrationTests(): array
    {
        return [
            'CollectionIntegrationTest',
            'UserImpersonationIntegrationTest',
            'UserImpersonationComprehensiveTest',
            'TranslationSystemComprehensiveTest',
            'SystemSettingsTest',
            'SystemSettingsGlobalScopesTest',
            'SystemSettingResourceTest',
            'SubscriberTest',
            'SubscriberResourceTest',
            'StockManagementTest',
            'SearchIntegrationTest',
            'ReferralSystemTest',
            'ReferralCodeResourceTest',
            'ReferralRecommendationGlobalScopesTest',
            'ProductHistoryTest',
            'ProductHistoryResourceTest',
            'NotificationStreamControllerTest',
            'NotificationServiceTest',
            'NotificationResourceTest',
            'NotificationControllerTest',
            'NewsResourceTest',
            'NewsGlobalScopesTest',
            'NewsControllerTest',
            'NewGlobalScopesTest',
            'MultilanguageTest',
            'MultilanguageSystemTest',
            'LocationResourceTest',
            'LocationGlobalScopesTest',
            'LocationControllerTest',
            'LiveNotificationServiceTest',
            'LegalResourceTest',
            'LegalControllerTest',
            'InventoryResourceTest',
            'InventoryManagementTest',
        ];
    }

    /**
     * Unit Tests
     */
    public static function getUnitTests(): array
    {
        return [
            'CollectionFeatureTest',
            'SliderTranslationTest',
            'SliderTest',
            'UserImpersonationWidgetsTest',
            'UserImpersonationSimpleTest',
            'UserImpersonationPageTest',
            'UserImpersonationBasicTest',
            'TranslationSystemTest',
            'TimeoutImplementationTest',
            'SystemSettingsWidgetsTest',
            'SimpleReportTest',
            'IconRenderingTest',
            'ExampleTest',
            'FilamentAdminPanelTest',
            'DatabaseSeedingTest',
            'DashboardTest',
            'ControllersSmokeTest',
            'CategoryAccordionMenuTest',
            'CanBeOneOfManyUltimateTest',
            'CanBeOneOfManyUltimateAdvancedTest',
            'CanBeOneOfManyFinalUltimateTest',
            'SystemSettingsTest',
            'SystemSettingsGlobalScopesTest',
            'SystemSettingResourceTest',
            'SubscriberTest',
            'SubscriberResourceTest',
            'StockManagementTest',
            'SkipWhileFunctionalityTest',
            'SimpleSplitInTest',
            'SimpleSkipWhileTest',
            'SimpleSkipWhileAdvancedTest',
            'SimpleRouteTest',
            'SimpleDashboardTest',
            'SeoDataResourceTest',
            'SeederTimeoutTest',
            'SearchIntegrationTest',
            'RouteTest',
            'ReviewTest',
            'ReportTest',
            'ReportResourceTest',
            'RegionTest',
            'ReferralTest',
            'ReferralSystemTest',
            'ReferralRewardResourceTest',
            'ReferralRecommendationGlobalScopesTest',
            'ReferralCodeResourceTest',
            'RedirectsTest',
            'RecommendationSystemTest',
            'ProductHistoryTest',
            'ProductHistoryResourceTest',
            'NotificationStreamControllerTest',
            'NotificationServiceTest',
            'NotificationResourceTest',
            'NotificationControllerTest',
            'NewTimeoutImplementationTest',
            'NewsResourceTest',
            'NewsGlobalScopesTest',
            'NewsControllerTest',
            'NewGlobalScopesTest',
            'MultilanguageTest',
            'MultilanguageSystemTest',
            'LocationResourceTest',
            'LocationGlobalScopesTest',
            'LocationControllerTest',
            'LiveNotificationServiceTest',
            'LegalResourceTest',
            'LegalControllerTest',
            'LazyCollectionTimeoutTest',
            'InventoryResourceTest',
            'InventoryManagementTest',
        ];
    }

    /**
     * Controller Tests
     */
    public static function getControllerTests(): array
    {
        return [
            'NotificationStreamControllerTest',
            'NotificationControllerTest',
            'NewsControllerTest',
            'LocationControllerTest',
            'LegalControllerTest',
        ];
    }

    /**
     * Model Tests
     */
    public static function getModelTests(): array
    {
        return [
            'SystemSettingTest',
            'StockResourceTest',
            'SeoDataResourceTest',
            'ReviewTest',
            'ReportTest',
            'ReportResourceTest',
            'RegionTest',
            'ReferralTest',
            'ReferralSystemTest',
            'ReferralRewardResourceTest',
            'ReferralRecommendationGlobalScopesTest',
            'ReferralCodeResourceTest',
            'RecommendationSystemTest',
            'ProductHistoryTest',
            'ProductHistoryResourceTest',
            'NotificationServiceTest',
            'NotificationResourceTest',
            'NewsResourceTest',
            'NewsGlobalScopesTest',
            'NewGlobalScopesTest',
            'MultilanguageTest',
            'MultilanguageSystemTest',
            'LocationResourceTest',
            'LocationGlobalScopesTest',
            'LiveNotificationServiceTest',
            'LegalResourceTest',
            'InventoryResourceTest',
            'InventoryManagementTest',
        ];
    }

    /**
     * Page Tests
     */
    public static function getPageTests(): array
    {
        return [
            'UserImpersonationPageTest',
            'DashboardTest',
            'SimpleDashboardTest',
        ];
    }

    /**
     * Get all test groups
     */
    public static function getAllGroups(): array
    {
        return [
            'admin' => self::getAdminTests(),
            'widgets' => self::getWidgetTests(),
            'api' => self::getApiTests(),
            'integration' => self::getIntegrationTests(),
            'unit' => self::getUnitTests(),
            'controllers' => self::getControllerTests(),
            'models' => self::getModelTests(),
            'pages' => self::getPageTests(),
        ];
    }
}
