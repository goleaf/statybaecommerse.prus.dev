<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

final class ScanUninitializedPagesCommand extends Command
{
    protected $signature = 'filament:scan-uninitialized-pages';

    protected $description = 'Find Filament resource pages missing the static $resource declaration.';

    public function handle(): int
    {
        $base = base_path('app/Filament/Resources');
        $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($base));
        $bad = [];
        $validExtends = [
            'ListRecords',
            'CreateRecord',
            'EditRecord',
            'ViewRecord',
            'ManageRelatedRecords',
            'ManageRelatedPage',
            'ManageRecords',
            'RelationManager',
            'Page',
        ];

        foreach ($iterator as $file) {
            if ($file->isDir() || $file->getExtension() !== 'php') {
                continue;
            }

            $code = file_get_contents($file->getPathname());

            if ($code === false) {
                continue;
            }

            if (! preg_match('/class\s+([A-Za-z0-9_]+)\s+extends\s+([A-Za-z0-9_\\]+)/', $code, $matches)) {
                continue;
            }

            $extends = $matches[2];
            $short = str_contains($extends, '\\') ? substr($extends, strrpos($extends, '\\') + 1) : $extends;

            if (! in_array($short, $validExtends, true)) {
                continue;
            }

            if (! str_contains($file->getPathname(), DIRECTORY_SEPARATOR . 'Pages' . DIRECTORY_SEPARATOR)) {
                continue;
            }

            if (! preg_match('/protected\s+static\s+string\s+\$resource\s*=\s*[^;]+;/', $code)) {
                $bad[] = sprintf(
                    '%s (extends %s) missing protected static string $resource',
                    $file->getPathname(),
                    $short,
                );
            }
        }

        if ($bad === []) {
            $this->info('OK');

            return self::SUCCESS;
        }

        sort($bad);

        foreach ($bad as $message) {
            $this->error($message);
        }

        return self::FAILURE;
    }
}
