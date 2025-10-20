<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Console\Commands\Concerns\InteractsWithDataTransfer;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use RuntimeException;

final class DataImportCommand extends Command
{
    use InteractsWithDataTransfer;

    /**
     * @var string
     */
    protected $signature = 'data:import
        {entity : Supported entity name (categories, products, attributes)}
        {path : Source file path or - for stdin}
        {--format= : Input format (json or csv)}
        {--chunk=500 : Number of rows to process per batch}
        {--truncate : Truncate the destination table before import}';

    /**
     * @var string
     */
    protected $description = 'Import data for whitelisted entities from streaming CSV or newline-delimited JSON files.';

    public function handle(): int
    {
        $entity = strtolower((string) $this->argument('entity'));

        if (! $this->isSupportedEntity($entity)) {
            $this->error(sprintf(
                'Unsupported entity [%s]. Allowed entities: %s.',
                $entity,
                implode(', ', $this->supportedEntities())
            ));

            return self::FAILURE;
        }

        $rawPathArgument = $this->argument('path');

        $pathArgument = $rawPathArgument;

        $format = $this->resolveFormat($this->option('format'), $pathArgument === '-' ? null : $pathArgument);
        $path = $this->normalizeInputPath($pathArgument);

        if ($path !== 'php://stdin' && ! is_file($path)) {
            $this->error(sprintf('Input file [%s] was not found.', $path));

            return self::FAILURE;
        }

        $chunkSize = max(1, (int) $this->option('chunk'));
        $table = $this->tableFor($entity);

        if ($this->option('truncate')) {
            $this->truncateTable($table);
        }

        try {
            /** @var resource $handle */
            $handle = $this->openHandle($path, 'rb');
        } catch (RuntimeException $exception) {
            $this->error($exception->getMessage());

            return self::FAILURE;
        }

        try {
            $count = $this->importRows($format, $handle, $table, $chunkSize);
        } catch (RuntimeException $exception) {
            if (is_resource($handle)) {
                fclose($handle);
            }
            $this->error($exception->getMessage());

            return self::FAILURE;
        }

        if (is_resource($handle)) {
            fclose($handle);
        }

        $this->info(sprintf('Imported %d rows into %s.', $count, $table));

        return self::SUCCESS;
    }

    private function normalizeInputPath(string $path): string
    {
        if ($path === '-') {
            return 'php://stdin';
        }

        return $path;
    }

    private function truncateTable(string $table): void
    {
        Schema::disableForeignKeyConstraints();

        $connection = DB::connection();

        if ($connection->getDriverName() === 'sqlite') {
            $connection->table($table)->delete();

            try {
                $connection->statement('DELETE FROM sqlite_sequence WHERE name = ?', [$table]);
            } catch (\Throwable) {
                // Ignore if sqlite_sequence is unavailable.
            }
        } else {
            $connection->table($table)->truncate();
        }

        Schema::enableForeignKeyConstraints();
    }
}
