<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

final class FixAllNavigationGroupsRecursiveCommand extends Command
{
    protected $signature = 'filament:navigation-groups:fix-recursive';

    protected $description = 'Recursively normalize navigation group property definitions for all Filament resources.';

    public function handle(): int
    {
        $resourcePath = base_path('app/Filament/Resources');
        $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($resourcePath));

        $this->info('Fixing all navigation group issues (recursive)...');

        foreach ($iterator as $file) {
            if ($file->isDir() || $file->getExtension() !== 'php') {
                continue;
            }

            $content = file_get_contents($file->getPathname()) ?: '';
            $originalContent = $content;

            $content = preg_replace(
                '/(protected static \?\w+ \$model = [^;]+;)\s*\*\* @var UnitEnum\|string\|null \*\/\s*protected static \$navigationGroup/',
                '$1'.PHP_EOL.PHP_EOL.'    protected static string|\UnitEnum|null $navigationGroup',
                $content,
            );

            $content = preg_replace(
                '/protected static \?\w+ \$navigationGroup/',
                'protected static string|\UnitEnum|null $navigationGroup',
                $content,
            );

            $content = preg_replace(
                '/protected static \$navigationGroup/',
                'protected static string|\UnitEnum|null $navigationGroup',
                $content,
            );

            if ($content !== $originalContent) {
                file_put_contents($file->getPathname(), $content);
                $this->line('Fixed: '.basename($file));
            }
        }

        $this->info('All navigation group fixes completed (recursive).');

        return self::SUCCESS;
    }
}
