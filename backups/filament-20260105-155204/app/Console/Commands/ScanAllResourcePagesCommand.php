<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use ReflectionProperty;
use Throwable;

final class ScanAllResourcePagesCommand extends Command
{
    protected $signature = 'filament:scan-resource-pages';

    protected $description = 'Scan Filament resource pages to ensure static $resource is initialized.';

    public function handle(): int
    {
        $base = base_path('app');
        $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($base));
        $bad = [];
        $checked = 0;

        foreach ($iterator as $file) {
            if ($file->isDir() || $file->getExtension() !== 'php') {
                continue;
            }

            $code = file_get_contents($file->getPathname());

            if ($code === false) {
                continue;
            }

            if (! preg_match('/^namespace\s+([^;]+);/m', $code, $namespaceMatches)) {
                continue;
            }

            if (! preg_match('/class\s+([A-Za-z0-9_]+)/', $code, $classMatches)) {
                continue;
            }

            $class = sprintf('%s\\%s', $namespaceMatches[1], $classMatches[1]);

            try {
                if (! class_exists($class)) {
                    continue;
                }

                if (! is_subclass_of($class, \Filament\Resources\Pages\Page::class)) {
                    continue;
                }

                $checked++;

                try {
                    $property = new ReflectionProperty($class, 'resource');

                    if ($property->isStatic() && ! $property->isInitialized()) {
                        $bad[] = sprintf('%s -> %s', $class, $file->getPathname());

                        continue;
                    }

                    $class::getResource();
                } catch (Throwable $exception) {
                    $bad[] = sprintf(
                        '%s -> %s :: %s :: %s',
                        $class,
                        $file->getPathname(),
                        $exception::class,
                        $exception->getMessage(),
                    );
                }
            } catch (Throwable) {
                continue;
            }
        }

        if ($bad === []) {
            $this->info(sprintf('OK checked %d', $checked));

            return self::SUCCESS;
        }

        sort($bad);

        foreach ($bad as $message) {
            $this->error($message);
        }

        return self::FAILURE;
    }
}
