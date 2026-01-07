<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;

final class FixSimpleFilamentIssuesCommand extends Command
{
    protected $signature = 'filament:fix-simple-issues';

    protected $description = 'Fix the most common Filament v4 issues across a curated set of resources.';

    public function handle(): int
    {
        $this->components->info('=== SIMPLE FILAMENT V4 ISSUES FIX ===');

        $criticalResources = [
            'NewsImageResource',
            'NewsResource',
            'NewsTagResource',
            'NormalSettingResource',
            'CartItemResource',
            'LocationResource',
            'OrderResource',
            'PartnerResource',
            'PartnerTierResource',
            'ProductComparisonResource',
            'ProductFeatureResource',
            'ProductHistoryResource',
            'ProductImageResource',
            'ProductVariantResource',
            'RecommendationConfigResource',
            'ReportResource',
            'ReviewResource',
            'SeoDataResource',
            'SettingResource',
            'StockMovementResource',
            'SubscriberResource',
            'SystemSettingResource',
            'UserBehaviorResource',
            'UserPreferenceResource',
            'UserWishlistResource',
            'VariantAttributeValueResource',
            'VariantCombinationResource',
            'VariantImageResource',
            'VariantInventoryResource',
            'VariantPriceHistoryResource',
            'VariantPricingRuleResource',
        ];

        $fixedFiles = [];
        $errors = [];

        foreach ($criticalResources as $resource) {
            $file = base_path('app/Filament/Resources/' . $resource . '.php');

            if (! file_exists($file)) {
                $this->error('❌ File not found: ' . $file);

                continue;
            }

            $content = file_get_contents($file) ?: '';
            $originalContent = $content;

            $this->output->write('Processing: ' . $resource . '... ');

            $lines = explode(PHP_EOL, $content);
            $uniqueImports = [];
            $newLines = [];

            foreach ($lines as $line) {
                if (str_starts_with($line, 'use ')) {
                    if (! in_array($line, $uniqueImports, true)) {
                        $uniqueImports[] = $line;
                        $newLines[] = $line;
                    }
                } else {
                    $newLines[] = $line;
                }
            }

            $content = implode(PHP_EOL, $newLines);
            $content = str_replace('use Filament\Forms\Form;', 'use Filament\Schemas\Schema;', $content);
            $content = str_replace('public static function form(Form $form): Form', 'public static function form(Schema $schema): Schema', $content);
            $content = str_replace('return $form', 'return $schema', $content);

            $content = preg_replace('/protected static \?\w+ \$navigationGroup = ([^;]+);/', 'protected static $navigationGroup = $1;', $content);
            $content = preg_replace('/\s*\/\*\* @var UnitEnum\|string\|null \*\/\s*\n\s*protected static \$navigationGroup/', '    protected static $navigationGroup', $content);

            if ($content !== $originalContent) {
                if (file_put_contents($file, $content) !== false) {
                    $fixedFiles[] = $file;
                    $this->info('✅ FIXED');
                } else {
                    $errors[] = '❌ Failed to write: ' . $file;
                    $this->error('❌ WRITE ERROR');
                }
            } else {
                $this->info('✅ OK');
            }
        }

        $this->newline();
        $this->line('=== SUMMARY ===');
        $this->line('Files fixed: ' . count($fixedFiles));
        $this->line('Errors: ' . count($errors));

        if ($fixedFiles !== []) {
            $this->newline();
            $this->line('Fixed files:');

            foreach ($fixedFiles as $file) {
                $this->line('- ' . basename($file));
            }
        }

        if ($errors !== []) {
            $this->newline();
            $this->line('Errors:');

            foreach ($errors as $error) {
                $this->error($error);
            }
        }

        $this->newline();
        $this->line('=== VALIDATION ===');
        $this->line('Running syntax check on fixed files...');

        $syntaxErrors = [];
        $syntaxOk = [];

        foreach ($fixedFiles as $file) {
            $output = [];
            $returnCode = 0;
            exec(sprintf('php -l %s 2>&1', escapeshellarg($file)), $output, $returnCode);

            if ($returnCode !== 0) {
                $syntaxErrors[] = basename($file) . ': ' . implode(' ', $output);
            } else {
                $syntaxOk[] = basename($file);
            }
        }

        $this->line('✅ Files with valid syntax: ' . count($syntaxOk));
        $this->line('❌ Files with syntax errors: ' . count($syntaxErrors));

        if ($syntaxErrors !== []) {
            $this->newline();
            $this->line('Syntax errors found:');

            foreach ($syntaxErrors as $error) {
                $this->error('- ' . $error);
            }
        }

        $this->newline();
        $this->line('=== TESTING FILAMENT COMMANDS ===');
        $output = [];
        $returnCode = 0;
        exec('php artisan list 2>&1', $output, $returnCode);

        if ($returnCode === 0) {
            $this->info('✅ Filament commands working');
        } else {
            $this->error('❌ Filament commands still have errors:');
            $this->line(implode("\n", array_slice($output, 0, 10)));
        }

        $this->newline();
        $this->info('Done!');

        return $errors === [] && $syntaxErrors === [] && $returnCode === 0 ? self::SUCCESS : self::FAILURE;
    }
}
