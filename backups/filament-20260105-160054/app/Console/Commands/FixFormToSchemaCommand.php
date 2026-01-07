<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;

final class FixFormToSchemaCommand extends Command
{
    protected $signature = 'filament:fix-form-schema';

    protected $description = 'Update specific Filament resources to use the Schema API instead of the legacy Form API.';

    public function handle(): int
    {
        $this->components->info('=== FIXING FORM → SCHEMA COMPATIBILITY ===');

        $resourcesToFix = [
            'NewsResource',
            'NewsTagResource',
            'NormalSettingResource',
            'PriceListResource',
            'RecommendationBlockResource',
        ];

        $fixedCount = 0;
        $errorCount = 0;

        foreach ($resourcesToFix as $resource) {
            $file = base_path('app/Filament/Resources/' . $resource . '.php');

            if (! file_exists($file)) {
                $this->error(sprintf('❌ File not found: %s', $file));
                $errorCount++;

                continue;
            }

            $this->line('🔧 Fixing: ' . $resource);
            $content = file_get_contents($file) ?: '';
            $originalContent = $content;

            $content = preg_replace(
                '/public static function form\(Form \$form\): Form/',
                'public static function form(Schema $schema): Schema',
                $content,
            );

            $content = preg_replace(
                '/return \$form\s*->schema\(/',
                'return $schema->schema(',
                $content,
            );

            if (! Str::contains($content, 'use Filament\\Forms\\Form;')) {
                $content = preg_replace(
                    '/(use Filament\\Forms;)/',
                    "$1\nuse Filament\\Forms\\Form;",
                    $content,
                );
            }

            if ($content !== $originalContent && file_put_contents($file, $content) !== false) {
                $this->info('  ✅ Fixed: ' . $resource);
                $fixedCount++;
            } elseif ($content === $originalContent) {
                $this->line('  ⏭️  No changes needed: ' . $resource);
            } else {
                $this->error('  ❌ Failed to write: ' . $resource);
                $errorCount++;
            }
        }

        $this->newline();
        $this->line('=== SUMMARY ===');
        $this->line(sprintf('✅ Resources fixed: %d', $fixedCount));
        $this->line(sprintf('❌ Errors: %d', $errorCount));
        $this->newline();
        $this->info('Done!');

        return $errorCount === 0 ? self::SUCCESS : self::FAILURE;
    }
}
