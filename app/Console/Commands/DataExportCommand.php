<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Console\Commands\Concerns\InteractsWithDataTransfer;
use Illuminate\Console\Command;
use RuntimeException;

final class DataExportCommand extends Command
{
    use InteractsWithDataTransfer;

    /**
     * @var string
     */
    protected $signature = 'data:export
        {entity : Supported entity name (categories, products, attributes)}
        {path? : Destination file path or - for stdout}
        {--format= : Output format (json or csv)}';

    /**
     * @var string
     */
    protected $description = 'Export whitelisted entities to streaming CSV or newline-delimited JSON files.';

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

        $pathArgument = is_string($rawPathArgument) ? $rawPathArgument : null;

        $format = $this->resolveFormat($this->option('format'), $pathArgument);

        $path = $this->determineOutputPath($entity, $format, $pathArgument);

        if ($path !== 'php://output') {
            $this->ensureDirectory($path);
        }

        try {
            /** @var resource $handle */
            $handle = $this->openHandle($path, 'wb');
        } catch (RuntimeException $exception) {
            $this->error($exception->getMessage());

            return self::FAILURE;
        }

        try {
            $table = $this->tableFor($entity);
            $count = $this->exportRows($format, $handle, $table);
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

        if ($path === 'php://output') {
            $this->info(sprintf('Exported %d %s rows.', $count, $entity));
        } else {
            $this->info(sprintf('Exported %d rows to %s.', $count, $path));
        }

        return self::SUCCESS;
    }

    private function determineOutputPath(string $entity, string $format, ?string $pathArgument): string
    {
        if ($pathArgument === null) {
            return $this->defaultExportPath($entity, $format);
        }

        if ($pathArgument === '-') {
            return 'php://output';
        }

        return $pathArgument;
    }
}
