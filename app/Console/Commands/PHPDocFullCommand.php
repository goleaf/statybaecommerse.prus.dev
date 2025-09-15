<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class PHPDocFullCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'docs:full 
                            {--port=8080 : Port to serve documentation on}
                            {--host=localhost : Host to serve documentation on}
                            {--no-serve : Skip serving documentation after generation}';

    /**
     * The console command description.
     */
    protected $description = 'Complete PHPDoc workflow: clean, upgrade, generate, and serve documentation';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('🚀 Starting Complete PHPDoc Workflow...');
        $this->newLine();

        // Step 1: Clean existing documentation
        $this->info('Step 1/4: 🧹 Cleaning existing documentation...');
        $this->call('docs:clean', ['--force' => true]);
        $this->newLine();

        // Step 2: Upgrade PHPDoc comments
        $this->info('Step 2/4: ⬆️  Upgrading PHPDoc comments...');
        $this->call('docs:upgrade');
        $this->newLine();

        // Step 3: Generate documentation
        $this->info('Step 3/4: 📖 Generating documentation...');
        $this->call('docs:generate');
        $this->newLine();

        // Step 4: Serve documentation (optional)
        if (!$this->option('no-serve')) {
            $this->info('Step 4/4: 🌐 Serving documentation...');
            $this->newLine();
            
            $port = $this->option('port');
            $host = $this->option('host');
            
            $this->call('docs:serve', [
                '--port' => $port,
                '--host' => $host
            ]);
        } else {
            $this->info('Step 4/4: ⏭️  Skipping serve (--no-serve option used)');
            $this->newLine();
            $this->showCompletionMessage();
        }

        return Command::SUCCESS;
    }

    /**
     * Show completion message with next steps.
     */
    private function showCompletionMessage(): void
    {
        $this->info('🎉 Complete PHPDoc workflow finished!');
        $this->newLine();
        
        $this->line('📂 Documentation location: docs/html/');
        $this->line('🌐 To serve documentation: php artisan docs:serve');
        $this->line('🔗 Then open: http://localhost:8080');
        $this->newLine();
        
        $this->line('💡 Available commands:');
        $this->line('  • php artisan docs:generate  - Generate documentation only');
        $this->line('  • php artisan docs:upgrade   - Upgrade PHPDoc comments only');
        $this->line('  • php artisan docs:clean     - Clean documentation only');
        $this->line('  • php artisan docs:serve     - Serve documentation only');
        $this->line('  • php artisan docs:full      - Complete workflow');
    }
}
