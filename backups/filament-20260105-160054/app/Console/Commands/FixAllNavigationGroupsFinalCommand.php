<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

final class FixAllNavigationGroupsFinalCommand extends Command
{
    protected $signature = 'filament:navigation-groups:fix-final {--skip-tests : Skip running the application test suite.}';

    protected $description = 'Run the final comprehensive navigationGroup fix routine and refresh caches.';

    public function handle(): int
    {
        $projectRoot = base_path();
        $resourcesPath = $projectRoot . '/app/Filament/Resources';

        $this->components->info('🔧 Starting final comprehensive NavigationGroup fix...');

        $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($resourcesPath));
        $fixedCount = 0;
        $errorCount = 0;
        $files = [];

        foreach ($iterator as $file) {
            if ($file->isDir() || $file->getExtension() !== 'php') {
                continue;
            }

            $files[] = $file->getPathname();
        }

        $mapping = [
            'Products'           => 'NavigationGroup::Products->value',
            'Orders'             => 'NavigationGroup::Orders->value',
            'Users'              => 'NavigationGroup::Users->value',
            'Settings'           => 'NavigationGroup::Settings->value',
            'Analytics'          => 'NavigationGroup::Analytics->value',
            'Content'            => 'NavigationGroup::Content->value',
            'Content Management' => 'NavigationGroup::ContentManagement->value',
            'System'             => 'NavigationGroup::System->value',
            'Marketing'          => 'NavigationGroup::Marketing->value',
            'Inventory'          => 'NavigationGroup::Inventory->value',
            'Reports'            => 'NavigationGroup::Reports->value',
            'Locations'          => 'NavigationGroup::Locations->value',
            'Discounts'          => 'NavigationGroup::Discounts->value',
            'Campaigns'          => 'NavigationGroup::Campaigns->value',
            'News'               => 'NavigationGroup::News->value',
            'Referral System'    => 'NavigationGroup::Referral->value',
        ];

        foreach ($files as $file) {
            $relativePath = Str::after($file, $projectRoot . '/');
            $this->line('Processing: ' . $relativePath);

            $content = file_get_contents($file) ?: '';
            $originalContent = $content;

            if (! str_contains($content, 'navigationGroup')) {
                continue;
            }

            if (str_contains($content, 'NavigationGroup::') && ! str_contains($content, 'use App\\Enums\\NavigationGroup;')) {
                $content = $this->ensureNavigationGroupImport($content);
            }

            $content = preg_replace_callback(
                '/(protected static \$navigationGroup = NavigationGroup::[^;]+;)/',
                static function (array $matches): string {
                    $line = $matches[1];

                    return str_contains($line, '->value') ? $line : str_replace(';', '->value;', $line);
                },
                $content,
            );

            $content = preg_replace_callback(
                "/protected static \\$navigationGroup = '([^']+)';/",
                static function (array $matches) use ($mapping): string {
                    $value = $matches[1];

                    if (! array_key_exists($value, $mapping)) {
                        return $matches[0];
                    }

                    return sprintf('protected static $navigationGroup = %s;', $mapping[$value]);
                },
                $content,
            );

            $content = preg_replace_callback(
                '/(\*\* @var UnitEnum\|string\|null \*\/\s*)?protected static \$navigationGroup = ([^;]+);/',
                static fn (array $matches): string => '/** @var \UnitEnum|string|null */' . PHP_EOL . '    protected static $navigationGroup = ' . $matches[2] . ';',
                $content,
            );

            if ($content !== $originalContent) {
                if (file_put_contents($file, $content) !== false) {
                    $this->info('✅ Fixed: ' . $relativePath);
                    $fixedCount++;
                } else {
                    $this->error('❌ Error writing: ' . $relativePath);
                    $errorCount++;
                }
            } else {
                $this->line('⏭️  No changes needed: ' . $relativePath);
            }
        }

        $this->newline();
        $this->line('🎯 NavigationGroup Fix Complete!');
        $this->line('✅ Files fixed: ' . $fixedCount);
        $this->line('❌ Errors: ' . $errorCount);

        $this->newline();
        $this->line('🧹 Clearing caches...');
        $this->callSilent('config:clear');
        $this->callSilent('cache:clear');
        $this->callSilent('route:clear');
        $this->callSilent('view:clear');
        $this->callSilent('optimize:clear');

        if (! $this->option('skip-tests')) {
            $this->newline();
            $this->line('🧪 Testing fixes...');
            $exitCode = $this->call('test', ['--stop-on-failure' => true]);

            if ($exitCode !== self::SUCCESS) {
                $this->error('Tests reported failures. Review the output above for details.');
            }
        }

        $this->newline();
        $this->info('✨ Final comprehensive NavigationGroup fix completed!');

        return $errorCount === 0 ? self::SUCCESS : self::FAILURE;
    }

    private function ensureNavigationGroupImport(string $content): string
    {
        $lines = preg_split('/\r?\n/', $content) ?: [];
        $result = [];
        $importAdded = false;

        foreach ($lines as $line) {
            $result[] = $line;

            if (! $importAdded && str_starts_with(trim($line), 'namespace App\\Filament\\Resources')) {
                $result[] = '';
                $result[] = 'use App\\Enums\\NavigationGroup;';
                $importAdded = true;
            }
        }

        return implode(PHP_EOL, $result);
    }
}
