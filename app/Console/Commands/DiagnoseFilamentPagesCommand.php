<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Filament\Facades\Filament;
use Illuminate\Console\Command;
use Throwable;

final class DiagnoseFilamentPagesCommand extends Command
{
    protected $signature = 'filament:diagnose-pages';

    protected $description = 'Detect Filament pages that fail to initialize their static resource property.';

    public function handle(): int
    {
        $bad = [];
        $checked = 0;

        foreach (Filament::getPanels() as $panel) {
            foreach ($panel->getResources() as $resourceClass) {
                foreach ($resourceClass::getPages() as $pageClass) {
                    $checked++;

                    try {
                        $pageClass::getResource();
                    } catch (Throwable $exception) {
                        $bad[] = sprintf(
                            '%s :: %s :: %s',
                            $pageClass,
                            $exception::class,
                            $exception->getMessage(),
                        );
                    }
                }
            }
        }

        if ($bad !== []) {
            foreach ($bad as $message) {
                $this->error($message);
            }

            return self::FAILURE;
        }

        $this->info(sprintf('OK checked %d pages', $checked));

        return self::SUCCESS;
    }
}
