<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\XmlCatalogService;
use Illuminate\Console\Command;

final class ImportExportCatalogXml extends Command
{
    protected $signature = 'catalog:xml {action : import|export} {path : XML file path} {--only=all : all|categories|products}';

    protected $description = 'Import or export categories/products with translations via XML';

    public function handle(XmlCatalogService $service): int
    {
        $action = (string) $this->argument('action');
        $path = (string) $this->argument('path');
        $only = (string) $this->option('only');

        if (! in_array($action, ['import', 'export'], true)) {
            $this->error('Action must be import or export');

            return 1;
        }

        if ($action === 'import') {
            if (! is_file($path)) {
                $this->error("File not found: {$path}");

                return 1;
            }
            $res = $service->import($path, ['only' => $only]);
            $this->info('Import finished');
            $this->line('Categories: created '.$res['categories']['created'].', updated '.$res['categories']['updated']);
            $this->line('Products:   created '.$res['products']['created'].', updated '.$res['products']['updated']);

            return 0;
        }

        // export
        $xml = $service->export($path, ['only' => $only]);
        if ($xml === '') {
            $this->error('Export failed');

            return 1;
        }
        $this->info("Exported to {$path}");

        return 0;
    }
}
