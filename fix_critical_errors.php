<?php

/** Fix critical syntax errors in Filament resources */
echo "Fixing critical syntax errors...\n";

// List of files with known critical errors
$criticalFiles = [
    'app/Filament/Resources/ReportResource.php',
    'app/Filament/Resources/SystemSettingResource.php',
    'app/Filament/Resources/PostResource.php',
    'app/Filament/Resources/RecommendationConfigResource.php',
    'app/Filament/Resources/PriceListItemResource.php',
    'app/Filament/Resources/SeoDataResource.php',
    'app/Filament/Resources/PriceListResource.php',
    'app/Filament/Resources/StockResource.php',
    'app/Filament/Resources/PriceResource.php',
    'app/Filament/Resources/ReferralResource.php',
    'app/Filament/Resources/ReferralRewardResource.php',
    'app/Filament/Resources/ZoneResource.php',
    'app/Filament/Resources/ReviewResource.php',
    'app/Filament/Resources/SystemSettingsResource.php',
    'app/Filament/Resources/ProductVariantResource.php',
    'app/Filament/Resources/SubscriberResource.php',
    'app/Filament/Resources/RecommendationBlockResource.php',
    'app/Filament/Resources/UserResource.php',
    'app/Filament/Resources/VariantPricingRuleResource.php',
    'app/Filament/Resources/WishlistItemResource.php',
    'app/Filament/Resources/DiscountResource.php',
    'app/Filament/Resources/PartnerResource.php',
    'app/Filament/Resources/PartnerTierResource.php',
    'app/Filament/Resources/UserBehaviorResource.php',
    'app/Filament/Resources/UserPreferenceResource.php',
    'app/Filament/Resources/UserWishlistResource.php',
    'app/Filament/Resources/VariantAnalyticsResource.php',
    'app/Filament/Resources/VariantStockHistoryResource.php',
];

foreach ($criticalFiles as $file) {
    if (file_exists($file)) {
        echo "Processing $file...\n";

        $content = file_get_contents($file);
        $originalContent = $content;

        // Fix navigation group type
        $content = preg_replace(
            '/protected static string\s*\|\s*UnitEnum\s*\|\s*null\s+\$navigationGroup/',
            'protected static $navigationGroup',
            $content
        );

        // Fix missing method signatures
        $content = preg_replace(
            '/public static function form\([^)]*\)\s*:\s*[^{]*\{/',
            'public static function form(Schema $schema): Schema'."\n    {",
            $content
        );

        $content = preg_replace(
            '/public static function table\([^)]*\)\s*:\s*[^{]*\{/',
            'public static function table(Table $table): Table'."\n    {",
            $content
        );

        // Add missing imports
        if (strpos($content, 'use Filament\Schemas\Schema;') === false && strpos($content, 'public static function form(') !== false) {
            $content = str_replace(
                'use Filament\Tables\Table;',
                "use Filament\Tables\Table;\nuse Filament\Schemas\Schema;",
                $content
            );
        }

        // Fix unterminated comments
        $content = preg_replace('/\/\*[^*]*\*\/\s*$/', '', $content);

        // Fix missing return statements
        $content = preg_replace(
            '/public static function getNavigationGroup\(\):\s*\?string\s*\{\s*return\s*"[^"]*"->value;\s*\}/',
            'public static function getNavigationGroup(): ?string'."\n    {\n        return 'System';\n    }",
            $content
        );

        // Fix missing closing braces
        $content = preg_replace("/->defaultSort\('[^']*'\);\s*\$/", "->defaultSort('sort_order');"."\n    }", $content);

        // Fix missing method bodies
        $content = preg_replace(
            '/public static function getRelations\(\): array\s*\{\s*return \[\s*\/\/\s*\];\s*\}/',
            'public static function getRelations(): array'."\n    {\n        return [\n            //\n        ];\n    }",
            $content
        );

        // Fix missing pages method
        $content = preg_replace(
            "/public static function getPages\(\): array\s*\{\s*return \[\s*'index'\s*=>\s*Pages\\\\[^,]*,\s*'create'\s*=>\s*Pages\\\\[^,]*,\s*'view'\s*=>\s*Pages\\\\[^,]*,\s*'edit'\s*=>\s*Pages\\\\[^,]*,\s*\];\s*\}/",
            'public static function getPages(): array'."\n    {\n        return [\n            'index' => Pages\ListRecords::route('/'),\n            'create' => Pages\CreateRecord::route('/create'),\n            'view' => Pages\ViewRecord::route('/{record}'),\n            'edit' => Pages\EditRecord::route('/{record}/edit'),\n        ];\n    }",
            $content
        );

        if ($content !== $originalContent) {
            file_put_contents($file, $content);
            echo "  Fixed $file\n";
        }

        // Check syntax
        $output = [];
        $returnCode = 0;
        exec("php -l \"$file\" 2>&1", $output, $returnCode);

        if ($returnCode === 0) {
            echo "  ✓ Syntax OK\n";
        } else {
            echo "  ✗ Still has errors\n";
        }
    }
}

echo "Done!\n";
