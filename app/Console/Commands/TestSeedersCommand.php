<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;

final class TestSeedersCommand extends Command
{
    protected $signature = 'app:test-seeders';

    protected $description = 'Execute selected database seeders to verify they run without errors.';

    public function handle(): int
    {
        $this->info('Testing NotificationSeeder...');

        try {
            $seeder = new \Database\Seeders\NotificationSeeder();
            $seeder->run();
            $this->info('✅ NotificationSeeder completed successfully');
        } catch (\Throwable $exception) {
            $this->error('❌ NotificationSeeder failed: '.$exception->getMessage());
        }

        $this->newline();
        $this->info('Testing OrderSeeder...');

        try {
            $seeder = new \Database\Seeders\OrderSeeder();
            $seeder->run();
            $this->info('✅ OrderSeeder completed successfully');
        } catch (\Throwable $exception) {
            $this->error('❌ OrderSeeder failed: '.$exception->getMessage());
        }

        $this->newline();
        $this->info('Testing PartnerSeeder...');

        try {
            $seeder = new \Database\Seeders\PartnerSeeder();
            $seeder->run();
            $this->info('✅ PartnerSeeder completed successfully');
        } catch (\Throwable $exception) {
            $this->error('❌ PartnerSeeder failed: '.$exception->getMessage());
        }

        $this->newline();
        $this->info('Seeder testing completed.');

        return self::SUCCESS;
    }
}
