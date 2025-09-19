<?php

/**
 * Fix Section Component Imports
 * 
 * This script fixes all incorrect Section component imports
 * from Filament\Forms\Components\Section to Filament\Schemas\Components\Section
 */

echo "🔧 Fixing Section Component Imports...\n\n";

// Get all files that need fixing
$files = [
    'app/Filament/Resources/SystemResource.php',
    'app/Filament/Resources/VariantPricingRuleResource.php',
    'app/Filament/Resources/UserResource.php',
    'app/Filament/Resources/RecommendationBlockResource.php',
    'app/Filament/Resources/OrderItemResource.php',
    'app/Filament/Resources/CurrencyResource.php',
    'app/Filament/Resources/SubscriberResource.php',
    'app/Filament/Resources/ProductVariantResource.php',
    'app/Filament/Resources/NewsResource.php',
    'app/Filament/Resources/CouponResource.php',
    'app/Filament/Resources/CartItemResource.php',
    'app/Filament/Resources/AnalyticsEventResource.php',
    'app/Filament/Resources/StockResource.php',
    'app/Filament/Resources/ProductHistoryResource.php',
    'app/Filament/Resources/DiscountConditionResource.php',
    'app/Filament/Resources/CityResource.php',
    'app/Filament/Resources/AttributeResource.php',
    'app/Filament/Resources/SystemSettingsResource.php',
    'app/Filament/Resources/ReferralResource.php',
    'app/Filament/Resources/ReferralRewardResource.php',
    'app/Filament/Resources/PriceResource.php',
    'app/Filament/Resources/LocationResource.php',
    'app/Filament/Resources/CompanyResource.php',
    'app/Filament/Resources/CampaignResource.php',
    'app/Filament/Resources/ActivityLogResource.php',
    'app/Filament/Resources/RecommendationConfigResourceSimple.php',
    'app/Filament/Resources/PostResource.php',
    'app/Filament/Resources/DiscountCodeResource.php',
    'app/Filament/Resources/CategoryResource.php',
    'app/Filament/Resources/BrandResource.php',
    'app/Filament/Resources/SeoDataResource.php',
    'app/Filament/Resources/PriceListResource.php',
    'app/Filament/Resources/LegalResource.php',
    'app/Filament/Resources/CollectionResource.php',
    'app/Filament/Resources/AttributeValueResource.php',
    'app/Filament/Resources/ReviewResource.php',
    'app/Filament/Resources/PriceListItemResource.php',
    'app/Filament/Resources/CustomerManagementResource.php',
    'app/Filament/Resources/CampaignConversionResource.php',
    'app/Filament/Resources/AddressResource.php',
    'app/Filament/Resources/Countries/Schemas/CountryForm.php',
    'app/Filament/Resources/RecommendationConfigResource.php',
    'app/Filament/Resources/OrderResource.php',
    'app/Filament/Resources/CustomerGroupResource.php',
    'app/Filament/Resources/SystemSettingResource.php',
    'app/Filament/Resources/ZoneResource.php',
    'app/Filament/Resources/ReportResource.php',
    'app/Filament/Resources/ProductResource.php',
    'app/Filament/Resources/MenuResource.php',
    'app/Filament/Resources/CountryResource.php',
    'app/Filament/Resources/CampaignClickResource.php',
    'app/Filament/Resources/OrderResource/RelationManagers/OrderShippingRelationManager.php',
    'app/Filament/Resources/OrderResource/RelationManagers/OrderItemsRelationManager.php',
    'app/Filament/Resources/OrderResource/RelationManagers/OrderDocumentsRelationManager.php',
    'app/Filament/Pages/SliderManagement.php',
    'app/Services/MultiLanguageTabService.php',
    'app/Filament/Pages/Auth/EditProfile.php',
];

$fixedCount = 0;
$errorCount = 0;

foreach ($files as $file) {
    if (!file_exists($file)) {
        echo "⚠️  File not found: $file\n";
        continue;
    }
    
    $content = file_get_contents($file);
    $originalContent = $content;
    
    // Fix the import
    $content = str_replace(
        'use Filament\\Forms\\Components\\Section;',
        'use Filament\\Schemas\\Components\\Section;',
        $content
    );
    
    // Also fix any inline usage
    $content = str_replace(
        'Filament\\Forms\\Components\\Section',
        'Filament\\Schemas\\Components\\Section',
        $content
    );
    
    if ($content !== $originalContent) {
        if (file_put_contents($file, $content)) {
            echo "✅ Fixed: $file\n";
            $fixedCount++;
        } else {
            echo "❌ Failed to write: $file\n";
            $errorCount++;
        }
    } else {
        echo "ℹ️  No changes needed: $file\n";
    }
}

echo "\n📊 Summary:\n";
echo "✅ Files fixed: $fixedCount\n";
echo "❌ Errors: $errorCount\n";
echo "📁 Total files processed: " . count($files) . "\n";

echo "\n🎯 Next steps:\n";
echo "1. Run tests to verify the fixes\n";
echo "2. Check for any remaining Section import issues\n";
echo "3. Update any tests that might be affected\n";
