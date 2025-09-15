<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\TimeoutService;
use Illuminate\Console\Command;
use Illuminate\Support\LazyCollection;

final class DemonstrateTimeoutCommand extends Command
{
    protected $signature = 'demo:timeout 
                            {--timeout=10 : Timeout in seconds}
                            {--items=1000 : Number of items to process}
                            {--operation=numbers : Operation type (numbers, products, users)}';

    protected $description = 'Demonstrate LazyCollection takeUntilTimeout functionality';

    public function handle(): int
    {
        $timeout = (int) $this->option('timeout');
        $items = (int) $this->option('items');
        $operation = $this->option('operation');

        $this->info("🚀 Demonstrating takeUntilTimeout with {$timeout}s timeout for {$items} items");
        $this->newLine();

        match ($operation) {
            'numbers' => $this->demonstrateNumbers($timeout, $items),
            'products' => $this->demonstrateProducts($timeout),
            'users' => $this->demonstrateUsers($timeout),
            default => $this->error("Unknown operation: {$operation}")
        };

        return 0;
    }

    private function demonstrateNumbers(int $timeout, int $items): void
    {
        $this->info("📊 Processing infinite sequence of numbers...");
        
        $startTime = now();
        $processedCount = 0;
        
        LazyCollection::times(INF)
            ->takeUntilTimeout(now()->addSeconds($timeout))
            ->each(function (int $number) use (&$processedCount) {
                $processedCount++;
                
                // Simulate some work
                usleep(10000); // 10ms delay
                
                if ($processedCount % 100 === 0) {
                    $this->line("Processed {$processedCount} numbers...");
                }
            });

        $duration = now()->diffInSeconds($startTime);
        $this->info("✅ Processed {$processedCount} numbers in {$duration} seconds");
    }

    private function demonstrateProducts(int $timeout): void
    {
        $this->info("🛍️ Processing products with timeout...");
        
        if (!class_exists(\App\Models\Product::class)) {
            $this->warn("Product model not found, skipping product demonstration");
            return;
        }

        $startTime = now();
        $processedCount = 0;
        
        try {
            \App\Models\Product::query()
                ->cursor()
                ->takeUntilTimeout(now()->addSeconds($timeout))
                ->each(function ($product) use (&$processedCount) {
                    $processedCount++;
                    
                    // Simulate product processing
                    usleep(5000); // 5ms delay
                    
                    if ($processedCount % 50 === 0) {
                        $this->line("Processed {$processedCount} products...");
                    }
                });

            $duration = now()->diffInSeconds($startTime);
            $this->info("✅ Processed {$processedCount} products in {$duration} seconds");
            
        } catch (\Exception $e) {
            $this->error("Error processing products: " . $e->getMessage());
        }
    }

    private function demonstrateUsers(int $timeout): void
    {
        $this->info("👥 Processing users with timeout...");
        
        if (!class_exists(\App\Models\User::class)) {
            $this->warn("User model not found, skipping user demonstration");
            return;
        }

        $startTime = now();
        $processedCount = 0;
        
        try {
            \App\Models\User::query()
                ->cursor()
                ->takeUntilTimeout(now()->addSeconds($timeout))
                ->each(function ($user) use (&$processedCount) {
                    $processedCount++;
                    
                    // Simulate user processing
                    usleep(2000); // 2ms delay
                    
                    if ($processedCount % 25 === 0) {
                        $this->line("Processed {$processedCount} users...");
                    }
                });

            $duration = now()->diffInSeconds($startTime);
            $this->info("✅ Processed {$processedCount} users in {$duration} seconds");
            
        } catch (\Exception $e) {
            $this->error("Error processing users: " . $e->getMessage());
        }
    }
}
