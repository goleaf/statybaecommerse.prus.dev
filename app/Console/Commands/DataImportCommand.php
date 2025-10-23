<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Throwable;

final class DataImportCommand extends Command
{
    protected $signature = 'data:import';

    protected $description = 'Import data into the application.';

    public function handle(): int
    {
        return self::SUCCESS;
    }

    /**
     * @throws Throwable
     */
    protected function truncateTable(string $table): void
    {
        Schema::disableForeignKeyConstraints();

        try {
            DB::table($table)->truncate();
        } finally {
            Schema::enableForeignKeyConstraints();
        }
    }
}
