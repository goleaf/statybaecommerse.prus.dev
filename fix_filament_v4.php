<?php

declare(strict_types=1);

/**
 * Filament v4 Migration Script
 * 
 * This script fixes common Filament v4 compatibility issues:
 * 1. Changes $form to $schema in form methods
 * 2. Updates form method signatures
 * 3. Fixes navigation group types
 * 4. Adds missing imports
 */

$files = [
    'app/Filament/Resources/PriceListResource/RelationManagers/CustomerGroupsRelationManager.php',
    'app/Filament/Resources/PriceListResource/RelationManagers/ItemsRelationManager.php',
    'app/Filament/Resources/PriceListResource/RelationManagers/PartnersRelationManager.php',
    'app/Filament/Resources/BrandResource/RelationManagers/ProductsRelationManager.php',
    'app/Filament/Resources/BrandResource/RelationManagers/TranslationsRelationManager.php',
    'app/Filament/Resources/ProductHistoryResource.php',
    'app/Filament/Resources/ProductResource/RelationManagers/AttributesRelationManager.php',
    'app/Filament/Resources/ProductResource/RelationManagers/CategoriesRelationManager.php',
    'app/Filament/Resources/ProductResource/RelationManagers/CollectionsRelationManager.php',
    'app/Filament/Resources/ProductResource/RelationManagers/DocumentsRelationManager.php',
    'app/Filament/Resources/ProductResource/RelationManagers/ImagesRelationManager.php',
    'app/Filament/Resources/ProductResource/RelationManagers/ReviewsRelationManager.php',
    'app/Filament/Resources/ProductResource/RelationManagers/VariantsRelationManager.php',
    'app/Filament/Resources/CustomerGroupResource/RelationManagers/CampaignsRelationManager.php',
    'app/Filament/Resources/CustomerGroupResource/RelationManagers/DiscountsRelationManager.php',
    'app/Filament/Resources/CustomerGroupResource/RelationManagers/PriceListsRelationManager.php',
    'app/Filament/Resources/CustomerGroupResource/RelationManagers/UsersRelationManager.php',
    'app/Filament/Resources/StockResource.php',
    'app/Filament/Resources/RecommendationConfigResource.php',
    'app/Filament/Resources/PriceResource.php',
    'app/Filament/Resources/StockResource/RelationManagers/StockMovementsRelationManager.php',
    'app/Filament/Resources/CampaignConversionResource.php',
    'app/Filament/Resources/CityResource.php',
    'app/Filament/Resources/CurrencyResource.php',
    'app/Filament/Resources/ReferralResource.php',
    'app/Filament/Resources/ReferralRewardResource.php',
    'app/Filament/Resources/CustomerGroupResource.php',
    'app/Filament/Resources/ActivityLogResource.php',
    'app/Filament/Resources/AttributeResource/RelationManagers/ProductsRelationManager.php',
    'app/Filament/Resources/AttributeResource/RelationManagers/ValuesRelationManager.php',
    'app/Filament/Resources/CampaignClickResource.php',
    'app/Filament/Resources/CampaignResource.php',
    'app/Filament/Resources/CollectionResource/RelationManagers/ProductsRelationManager.php',
    'app/Filament/Resources/NewsResource/RelationManagers/CommentsRelationManager.php',
    'app/Filament/Resources/SeoDataResource.php',
    'app/Filament/Resources/RegionResource_backup.php',
    'app/Filament/Resources/CustomerManagementResource/RelationManagers/AddressesRelationManager.php',
    'app/Filament/Resources/CustomerManagementResource/RelationManagers/DocumentsRelationManager.php',
    'app/Filament/Resources/CustomerManagementResource/RelationManagers/OrdersRelationManager.php',
    'app/Filament/Resources/CustomerManagementResource/RelationManagers/ReviewsRelationManager.php',
    'app/Filament/Resources/CustomerManagementResource/RelationManagers/WishlistRelationManager.php',
    'app/Filament/Resources/CustomerManagementResource.php',
    'app/Filament/Resources/DiscountCodeResource.php',
    'app/Filament/Resources/DiscountConditionResource.php',
    'app/Filament/Resources/ReviewResource.php',
    'app/Filament/Resources/RegionResource.php',
    'app/Filament/Resources/CartItemResource/RelationManagers/ProductRelationManager.php',
    'app/Filament/Resources/CartItemResource/RelationManagers/UserRelationManager.php',
    'app/Filament/Resources/AttributeResource.php',
    'app/Filament/Resources/AttributeValueResource/RelationManagers/ProductsRelationManager.php',
    'app/Filament/Resources/AttributeValueResource/RelationManagers/TranslationsRelationManager.php',
    'app/Filament/Resources/AttributeValueResource/RelationManagers/VariantsRelationManager.php',
    'app/Filament/Resources/CategoryResource/RelationManagers/ChildrenRelationManager.php',
    'app/Filament/Resources/CategoryResource/RelationManagers/ProductsRelationManager.php',
    'app/Filament/Resources/OrderResource/RelationManagers/OrderDocumentsRelationManager.php',
    'app/Filament/Resources/OrderResource/RelationManagers/OrderItemsRelationManager.php',
    'app/Filament/Resources/OrderResource/RelationManagers/OrderShippingRelationManager.php',
    'app/Filament/Resources/SystemSettingsResource.php',
    'app/Filament/Resources/ZoneResource.php',
    'app/Filament/Resources/RecommendationBlockResource.php',
    'app/Filament/Resources/AnalyticsEventResource.php',
    'app/Filament/Resources/AttributeValueResource.php',
    'app/Filament/Resources/CartItemResource.php',
    'app/Filament/Resources/CityResource/RelationManagers/AddressesRelationManager.php',
    'app/Filament/Resources/CityResource/RelationManagers/ChildrenRelationManager.php',
    'app/Filament/Resources/CurrencyResource/RelationManagers/PricesRelationManager.php',
    'app/Filament/Resources/CurrencyResource/RelationManagers/ZonesRelationManager.php',
    'app/Filament/Resources/LocationResource.php',
    'app/Filament/Resources/NewsResource.php',
    'app/Filament/Resources/OrderResource.php',
    'app/Filament/Resources/PostResource.php',
    'app/Filament/Resources/PriceListItemResource.php',
    'app/Filament/Resources/CountryResource/RelationManagers/AddressesRelationManager.php',
    'app/Filament/Resources/CountryResource/RelationManagers/CitiesRelationManager.php',
    'app/Filament/Resources/CountryResource/RelationManagers/RegionsRelationManager.php',
    'app/Filament/Resources/CountryResource/RelationManagers/UsersRelationManager.php',
    'app/Filament/Resources/CountryResource/RelationManagers/CustomersRelationManager.php',
    'app/Filament/Resources/CollectionResource.php',
    'app/Filament/Resources/PriceListResource.php',
    'app/Filament/Resources/ZoneResource/RelationManagers/CountriesRelationManager.php',
    'app/Filament/Resources/ZoneResource/RelationManagers/DiscountsRelationManager.php',
    'app/Filament/Resources/ZoneResource/RelationManagers/OrdersRelationManager.php',
    'app/Filament/Resources/ZoneResource/RelationManagers/PriceListsRelationManager.php',
];

$fixed = 0;
$errors = 0;

foreach ($files as $file) {
    if (!file_exists($file)) {
        echo "File not found: $file\n";
        $errors++;
        continue;
    }
    
    $content = file_get_contents($file);
    $originalContent = $content;
    
    // Fix 1: Change $form to $schema in form methods
    $content = preg_replace('/return \$form\s*->/m', 'return $schema->', $content);
    
    // Fix 2: Update form method signature if needed
    $content = preg_replace('/public static function form\([^)]*\): [^{]*\{/m', 'public static function form(Schema $schema): Schema {', $content);
    
    // Fix 3: Add missing Schema import if needed
    if (strpos($content, 'use Filament\Schemas\Schema;') === false && strpos($content, 'public static function form(') !== false) {
        $content = preg_replace('/(use Filament\\\\.*?;)/m', "$1\nuse Filament\Schemas\Schema;", $content, 1);
    }
    
    // Fix 4: Fix navigation group type
    $content = preg_replace('/protected static \?\w+ \$navigationGroup = ([^;]+);/m', '/** @var UnitEnum|string|null */\n    protected static $navigationGroup = $1;', $content);
    
    // Fix 5: Add UnitEnum import if needed
    if (strpos($content, 'protected static $navigationGroup') !== false && strpos($content, 'use UnitEnum;') === false) {
        $content = preg_replace('/(use Filament\\\\.*?;)/m', "$1\nuse UnitEnum;", $content, 1);
    }
    
    if ($content !== $originalContent) {
        file_put_contents($file, $content);
        echo "Fixed: $file\n";
        $fixed++;
    }
}

echo "\nSummary:\n";
echo "Files fixed: $fixed\n";
echo "Errors: $errors\n";
echo "Total processed: " . count($files) . "\n";
