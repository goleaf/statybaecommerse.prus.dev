<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Filament\Resources\BrandResource;
use Illuminate\Console\Command;

final class TestBrandResourceCommand extends Command
{
    protected $signature = 'app:test-brand-resource';

    protected $description = 'Output basic information from the BrandResource class.';

    public function handle(): int
    {
        $this->info('BrandResource class loaded successfully');
        $this->line('Model: '.BrandResource::getModel());
        $this->line('Navigation Icon: '.BrandResource::getNavigationIcon());
        $this->line('Navigation Group: '.BrandResource::getNavigationGroup());

        return self::SUCCESS;
    }
}
