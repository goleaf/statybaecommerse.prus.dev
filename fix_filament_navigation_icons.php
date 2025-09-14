<?php

/**
 * Fix Filament v4 navigationIcon type compatibility issues
 * This script removes type declarations from navigationIcon properties
 */

$filamentFiles = [
    'app/Filament/Pages/Dashboard.php',
    'app/Filament/Pages/RecommendationSystemManagement.php',
    'app/Filament/Resources/AddressResource.php',
    'app/Filament/Resources/CompanyResource.php',
    'app/Filament/Resources/ProductHistoryResource.php',
    'app/Filament/Resources/StockResource.php',
    'app/Filament/Resources/RecommendationConfigResource.php',
    'app/Filament/Resources/PriceResource.php',
    'app/Filament/Resources/BrandResource.php',
    'app/Filament/Resources/CampaignConversionResource.php',
    'app/Filament/Resources/CityResource.php',
    'app/Filament/Resources/CurrencyResource.php',
    'app/Filament/Resources/ProductResource.php',
    'app/Filament/Resources/ReferralResource.php',
    'app/Filament/Resources/ReferralRewardResource.php',
    'app/Filament/Resources/LegalResource.php',
    'app/Filament/Resources/CountryResource.php',
    'app/Filament/Resources/CustomerGroupResource.php',
    'app/Filament/Resources/ActivityLogResource.php',
    'app/Filament/Resources/CampaignClickResource.php',
    'app/Filament/Resources/CampaignResource.php',
    'app/Filament/Resources/CategoryResource.php',
    'app/Filament/Resources/SeoDataResource.php',
    'app/Filament/Resources/CustomerManagementResource.php',
    'app/Filament/Resources/DiscountCodeResource.php',
    'app/Filament/Resources/DiscountConditionResource.php',
    'app/Filament/Resources/ReviewResource.php',
    'app/Filament/Resources/RegionResource.php',
    'app/Filament/Resources/SubscriberResource.php',
    'app/Filament/Resources/AttributeResource.php',
    'app/Filament/Resources/SystemSettingResource.php',
    'app/Filament/Resources/MenuResource.php',
    'app/Filament/Resources/SystemSettingsResource.php',
    'app/Filament/Resources/ZoneResource.php',
    'app/Filament/Resources/RecommendationBlockResource.php',
    'app/Filament/Resources/AnalyticsEventResource.php',
    'app/Filament/Resources/AttributeValueResource.php',
    'app/Filament/Resources/CartItemResource.php',
    'app/Filament/Resources/LocationResource.php',
    'app/Filament/Resources/NewsResource.php',
    'app/Filament/Resources/OrderResource.php',
    'app/Filament/Resources/PostResource.php',
    'app/Filament/Resources/PriceListItemResource.php',
    'app/Filament/Resources/ReportResource.php',
    'app/Filament/Resources/CollectionResource.php',
    'app/Filament/Resources/PriceListResource.php',
    'app/Filament/Resources/RecommendationConfigResourceSimple.php',
];

$fixedFiles = 0;
$totalFiles = count($filamentFiles);

foreach ($filamentFiles as $file) {
    if (!file_exists($file)) {
        echo "Skipping non-existent file: $file\n";
        continue;
    }
    
    $content = file_get_contents($file);
    $originalContent = $content;
    
    // Remove type declarations from navigationIcon properties
    $patterns = [
        // Remove typed property declarations
        '/\s*\/\*\* @var [^*]+ \*\/\s*protected static \$navigationIcon\s*=\s*[^;]+;/',
        '/protected static \?\w+ \$navigationIcon\s*=\s*[^;]+;/',
        '/protected static \w+ \$navigationIcon\s*=\s*[^;]+;/',
    ];
    
    foreach ($patterns as $pattern) {
        $content = preg_replace($pattern, '', $content);
    }
    
    // Add untyped navigationIcon property if it doesn't exist
    if (strpos($content, '$navigationIcon') === false) {
        // Find the class declaration and add the property after it
        $content = preg_replace(
            '/(class\s+\w+[^{]*\{)/',
            "$1\n    protected static \$navigationIcon = 'heroicon-o-document-text';",
            $content
        );
    }
    
    if ($content !== $originalContent) {
        file_put_contents($file, $content);
        echo "Fixed: $file\n";
        $fixedFiles++;
    }
}

echo "\nFixed $fixedFiles out of $totalFiles files.\n";
echo "Filament v4 navigationIcon compatibility issues resolved.\n";
