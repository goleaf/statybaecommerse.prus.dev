<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class OptimizeForProduction extends Command
{
    protected $signature = 'optimize:production {--force : Force optimization even if not in production}';

    protected $description = 'Run all Laravel optimizations for production deployment';

    public function handle(): int
    {
        if (! app()->isProduction() && ! $this->option('force')) {
            $this->error('This command should only be run in production. Use --force to override.');

            return self::FAILURE;
        }

        $this->info('Starting production optimizations...');

        $commands = config('performance.framework.optimization_commands', [
            'config:cache',
            'route:cache',
            'view:cache',
            'event:cache',
        ]);

        foreach ($commands as $command) {
            $this->info("Running: php artisan {$command}");

            $exitCode = Artisan::call($command);

            if ($exitCode !== 0) {
                $this->error("Failed to run: {$command}");

                return self::FAILURE;
            }

            $this->line(Artisan::output());
        }

        // Warm critical caches
        if (config('performance.cache.enable_warming', true)) {
            $this->info('Warming critical caches...');
            Artisan::call('cache:warm');
            $this->line(Artisan::output());
        }

        $this->info('Production optimizations completed successfully!');

        return self::SUCCESS;
    }
}
