<?php

/** Script to fix common Filament v4 syntax errors */
$files = [
    'app/Filament/Resources/CustomerGroupResource/RelationManagers/DiscountsRelationManager.php',
    'app/Filament/Resources/CustomerGroupResource/RelationManagers/UsersRelationManager.php',
    'app/Filament/Resources/ProductResource/RelationManagers/CollectionsRelationManager.php',
    'app/Filament/Resources/ProductResource/RelationManagers/DocumentsRelationManager.php',
    'app/Filament/Resources/ProductResource/RelationManagers/ReviewsRelationManager.php',
    'app/Filament/Resources/ProductResource/RelationManagers/CategoriesRelationManager.php',
    'app/Filament/Resources/UserManagementResource/RelationManagers/AddressesRelationManager.php',
    'app/Filament/Resources/UserManagementResource/RelationManagers/OrdersRelationManager.php',
    'app/Filament/Resources/UserManagementResource/RelationManagers/ReviewsRelationManager.php',
    'app/Filament/Resources/AttributeResource/RelationManagers/ValuesRelationManager.php',
    'app/Filament/Resources/DiscountCodeResource/RelationManagers/DocumentsRelationManager.php',
    'app/Filament/Resources/DiscountCodeResource/RelationManagers/OrdersRelationManager.php',
    'app/Filament/Resources/DiscountCodeResource/RelationManagers/UsersRelationManager.php',
    'app/Filament/Resources/ZoneResource/RelationManagers/DiscountsRelationManager.php',
    'app/Filament/Resources/ZoneResource/RelationManagers/PriceListsRelationManager.php',
    'app/Filament/Resources/ZoneResource/RelationManagers/CountriesRelationManager.php',
    'app/Filament/Resources/ZoneResource/RelationManagers/OrdersRelationManager.php',
    'app/Filament/Resources/UserResource/RelationManagers/AddressesRelationManager.php',
    'app/Filament/Resources/UserResource/RelationManagers/DocumentsRelationManager.php',
    'app/Filament/Resources/UserResource/RelationManagers/OrdersRelationManager.php',
    'app/Filament/Resources/UserResource/RelationManagers/ReferralsRelationManager.php',
    'app/Filament/Resources/UserResource/RelationManagers/ReviewsRelationManager.php',
    'app/Filament/Resources/UserResource/RelationManagers/WishlistRelationManager.php',
    'app/Filament/Resources/UserResource/RelationManagers/ActivityLogRelationManager.php',
    'app/Filament/Resources/VariantStockResource/RelationManagers/StockMovementsRelationManager.php',
    'app/Filament/Resources/BrandResource/RelationManagers/ProductsRelationManager.php',
    'app/Filament/Resources/CollectionResource/RelationManagers/DocumentsRelationManager.php',
    'app/Filament/Resources/CollectionResource/RelationManagers/ProductsRelationManager.php',
    'app/Filament/Resources/CollectionResource/RelationManagers/TranslationsRelationManager.php',
    'app/Filament/Resources/CurrencyResource/RelationManagers/PricesRelationManager.php',
    'app/Filament/Resources/CurrencyResource/RelationManagers/ZonesRelationManager.php',
    'app/Filament/Resources/StockResource/RelationManagers/StockMovementsRelationManager.php',
];

foreach ($files as $file) {
    if (file_exists($file)) {
        echo "Fixing $file...\n";

        $content = file_get_contents($file);

        // Fix common syntax errors
        $content = preg_replace('/public function form\(Schema \$schema\): Schema/', 'public function form(Schema $schema): Schema', $content);
        $content = preg_replace('/->required\(\),\s*->maxLength/', '->required()\n                    ->maxLength', $content);
        $content = preg_replace('/->searchable\(\),\s*->sortable\(\),/', '->searchable()\n                    ->sortable(),', $content);
        $content = preg_replace('/->sortable\(\),\s*->badge\(\),\s*->color/', '->sortable()\n                    ->badge()\n                    ->color', $content);
        $content = preg_replace('/->dateTime\(\),\s*->toggleable/', '->dateTime()\n                    ->toggleable', $content);

        // Fix missing imports
        if (strpos($content, 'use Filament\Schemas\Schema;') === false) {
            $content = str_replace('use Filament\Tables\Table;', "use Filament\Tables\Table;\nuse Filament\Schemas\Schema;", $content);
        }

        // Fix double opening braces
        $content = preg_replace('/public function table\(Table \$table\): Table\s*\{\s*\{/', 'public function table(Table $table): Table' . "\n    {", $content);

        file_put_contents($file, $content);
        echo "Fixed $file\n";
    }
}

echo "Done fixing relation managers!\n";
