<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;

final class SearchIndexRebuildCommand extends Command
{
    protected $signature = 'search:index:rebuild {--only= : Comma separated list of targets (product,category,brand)}';

    protected $description = 'Flush and rebuild search indexes for all searchable models.';

    public function handle(): int
    {
        return $this->call('search:index', [
            '--fresh' => true,
            '--only' => $this->option('only'),
        ]);
    }
}
