<?php

// Script to fix navigationGroup type issues in Filament resources
// Convert NavigationGroup::EnumValue to string literals

$files = [
    'app/Filament/Resources/VariantPricingRuleResource.php',
    'app/Filament/Resources/ZoneResource.php',
    'app/Filament/Resources/StockResource.php',
    'app/Filament/Resources/SystemSettingsResource.php',
    'app/Filament/Resources/ReviewResource.php',
    'app/Filament/Resources/SeoDataResource.php',
    'app/Filament/Resources/RecommendationBlockResource.php',
    'app/Filament/Resources/RecommendationConfigResource.php',
    'app/Filament/Resources/PriceResource.php',
    'app/Filament/Resources/PostResource.php',
    'app/Filament/Resources/OrderItemResource.php',
    'app/Filament/Resources/NewsResource.php',
    'app/Filament/Resources/LocationResource.php',
    'app/Filament/Resources/CurrencyResource.php',
    'app/Filament/Resources/LegalResource.php',
    'app/Filament/Resources/CityResource.php',
    'app/Filament/Resources/CollectionResource.php',
    'app/Filament/Resources/CompanyResource.php',
    'app/Filament/Resources/CartItemResource.php',
    'app/Filament/Resources/AnalyticsEventResource.php',
    'app/Filament/Resources/AttributeResource.php',
    'app/Filament/Resources/AttributeValueResource.php',
    'app/Filament/Resources/SubscriberResource.php',
    'app/Filament/Resources/ReferralResource.php',
    'app/Filament/Resources/ReferralRewardResource.php',
    'app/Filament/Resources/DiscountCodeResource.php',
    'app/Filament/Resources/DiscountConditionResource.php',
    'app/Filament/Resources/CustomerManagementResource.php',
    'app/Filament/Resources/CouponResource.php',
    'app/Filament/Resources/CampaignConversionResource.php',
];

$replacements = [
    'NavigationGroup::Products' => "'Products'",
    'NavigationGroup::Orders' => "'Orders'",
    'NavigationGroup::Users' => "'Users'",
    'NavigationGroup::Settings' => "'Settings'",
    'NavigationGroup::Analytics' => "'Analytics'",
    'NavigationGroup::Content' => "'Content'",
    'NavigationGroup::System' => "'System'",
    'NavigationGroup::Marketing' => "'Marketing'",
    'NavigationGroup::Inventory' => "'Inventory'",
    'NavigationGroup::Reports' => "'Reports'",
    'NavigationGroup::Locations' => "'Locations'",
    'NavigationGroup::Referral' => "'Referral System'",
];

foreach ($files as $file) {
    if (file_exists($file)) {
        $content = file_get_contents($file);
        $originalContent = $content;

        foreach ($replacements as $search => $replace) {
            $content = str_replace($search, $replace, $content);
        }

        // Also fix the type declaration
        $content = preg_replace(
            '/protected static \$navigationGroup = /',
            'protected static string|null $navigationGroup = ',
            $content
        );

        if ($content !== $originalContent) {
            file_put_contents($file, $content);
            echo "Fixed: $file\n";
        }
    }
}

echo "Navigation group fixes completed!\n";

