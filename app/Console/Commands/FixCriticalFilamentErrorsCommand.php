<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;

final class FixCriticalFilamentErrorsCommand extends Command
{
    protected $signature = 'filament:fix-critical-errors';

    protected $description = 'Apply quick fixes for known critical syntax issues in Filament resources.';

    public function handle(): int
    {
        $this->info('Fixing critical syntax errors...');

        $criticalFiles = [
            'ReportResource',
            'SystemSettingResource',
            'PostResource',
            'RecommendationConfigResource',
            'PriceListItemResource',
            'SeoDataResource',
            'PriceListResource',
            'StockResource',
            'PriceResource',
            'ReferralResource',
            'ReferralRewardResource',
            'ReviewResource',
            'SystemSettingsResource',
            'ProductVariantResource',
            'SubscriberResource',
            'RecommendationBlockResource',
            'UserResource',
            'VariantPricingRuleResource',
            'WishlistItemResource',
            'DiscountResource',
            'PartnerResource',
            'PartnerTierResource',
            'UserBehaviorResource',
            'UserPreferenceResource',
            'UserWishlistResource',
            'VariantAnalyticsResource',
            'VariantStockHistoryResource',
        ];

        foreach ($criticalFiles as $resource) {
            $file = base_path('app/Filament/Resources/'.$resource.'.php');

            if (! file_exists($file)) {
                continue;
            }

            $this->line('Processing '.$file.'...');

            $content = file_get_contents($file) ?: '';
            $originalContent = $content;

            $content = preg_replace(
                '/protected static string\s*\|\s*UnitEnum\s*\|\s*null\s+\$navigationGroup/',
                'protected static $navigationGroup',
                $content,
            );

            $content = preg_replace(
                '/public static function form\([^)]*\)\s*:\s*[^{]*\{/',
                "public static function form(Schema \$schema): Schema\n    {",
                $content,
            );

            $content = preg_replace(
                '/public static function table\([^)]*\)\s*:\s*[^{]*\{/',
                "public static function table(Table \$table): Table\n    {",
                $content,
            );

            if (! str_contains($content, 'use Filament\Schemas\Schema;') && str_contains($content, 'public static function form(')) {
                $content = str_replace(
                    'use Filament\Tables\Table;',
                    "use Filament\Tables\Table;\nuse Filament\\Schemas\\Schema;",
                    $content,
                );
            }

            $content = preg_replace('/\/\*[^*]*\*\/\s*$/', '', $content);

            $content = preg_replace(
                '/public static function getNavigationGroup\(\):\s*\?string\s*\{\s*return\s*"[^"]*"->value;\s*\}/',
                "public static function getNavigationGroup(): ?string\n    {\n        return 'System';\n    }",
                $content,
            );

            $content = preg_replace(
                "/->defaultSort\('[^']*'\);\s*$/",
                "->defaultSort('sort_order');\n    }",
                $content,
            );

            $content = preg_replace(
                '/public static function getRelations\(\): array\s*\{\s*return \[\s*\/\/\s*\];\s*\}/',
                "public static function getRelations(): array\n    {\n        return [\n            //\n        ];\n    }",
                $content,
            );

            $content = preg_replace(
                "/public static function getPages\(\): array\s*\{\s*return \[\s*'index'\s*=>\s*Pages\\\\[^,]*,\s*'create'\s*=>\s*Pages\\\\[^,]*,\s*'view'\s*=>\s*Pages\\\\[^,]*,\s*'edit'\s*=>\s*Pages\\\\[^,]*,\s*\];\s*\}/",
                "public static function getPages(): array\n    {\n        return [\n            'index' => Pages\\ListRecords::route('/'),\n            'create' => Pages\\CreateRecord::route('/create'),\n            'view' => Pages\\ViewRecord::route('/{record}'),\n            'edit' => Pages\\EditRecord::route('/{record}/edit'),\n        ];\n    }",
                $content,
            );

            if ($content !== $originalContent) {
                file_put_contents($file, $content);
            }

            $output = [];
            $returnCode = 0;
            exec(sprintf('php -l %s 2>&1', escapeshellarg($file)), $output, $returnCode);

            if ($returnCode === 0) {
                $this->info('  ✓ Syntax OK');
            } else {
                $this->error('  ✗ Still has errors');
            }
        }

        $this->info('Done!');

        return self::SUCCESS;
    }
}
