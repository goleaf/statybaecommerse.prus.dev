<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Console\Commands\Concerns\InteractsWithDataTransfer;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use RuntimeException;
use Throwable;

final class DataImportCommand extends Command
{
    use InteractsWithDataTransfer;

    /**
     * Artisan signature for triggering the consolidated data import pipeline.
     *
     * @var string
     */
    protected $signature = 'data:import
        {entity : Supported entity name (categories, products, attributes)}
        {path? : Source file path or - for stdin}
        {--format= : Input format (json or csv)}
        {--chunk=500 : Rows to process per batch during import}
        {--truncate : Truncate the destination table before importing}';

    /**
     * Human readable description surfaced in `php artisan list` for discoverability.
     *
     * @var string
     */
    protected $description = 'Import data into the application.';

    public function handle(): int
    {
        // Resolve the entity we are about to import and guard against unsupported options early.
        $entity = strtolower((string) $this->argument('entity'));

        if (! $this->isSupportedEntity($entity)) {
            $this->error(sprintf(
                'Unsupported entity [%s]. Allowed entities: %s.',
                $entity,
                implode(', ', $this->supportedEntities())
            ));

            return self::FAILURE;
        }

        // Normalise the optional CLI arguments so format detection and handle opening behave consistently.
        $rawPathArgument = $this->argument('path');
        $pathArgument = is_string($rawPathArgument) ? $rawPathArgument : null;
        $format = $this->resolveFormat($this->option('format'), $pathArgument);
        $path = $this->determineInputPath($entity, $format, $pathArgument);

        if ($path !== 'php://stdin' && ! file_exists($path)) {
            $this->error(sprintf('The provided import file [%s] could not be located.', $path));

            return self::FAILURE;
        }

        $chunkSize = (int) $this->option('chunk');
        $chunkSize = $chunkSize > 0 ? $chunkSize : 500;

        // Ensure the import either truncates or gracefully upserts depending on the operator preference.
        if ($this->option('truncate')) {
            $this->truncateTable($this->tableFor($entity));
        }

        try {
            /** @var resource $handle */
            $handle = $this->openHandle($path, 'rb');
        } catch (RuntimeException $exception) {
            $this->error($exception->getMessage());

            return self::FAILURE;
        }

        try {
            // Delegate to the shared pipeline that parses the file and upserts in manageable batches.
            $count = $this->importRows($format, $handle, $this->tableFor($entity), $chunkSize);
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

        $this->info(sprintf('Imported %d %s rows using %s format.', $count, $entity, strtoupper($format)));

        return self::SUCCESS;
    }

    private function determineInputPath(string $entity, string $format, ?string $pathArgument): string
    {
        // Mirror the export command by allowing a conventional default when no explicit file is supplied.
        if ($pathArgument === null) {
            return storage_path('app/imports/' . $entity . '.' . $format);
        }

        if ($pathArgument === '-') {
            return 'php://stdin';
        }

        return $pathArgument;
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
