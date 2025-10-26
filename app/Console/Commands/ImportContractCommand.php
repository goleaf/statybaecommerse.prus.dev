<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\DataTransfer\DataTransferManager;
use Illuminate\Console\Command;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use InvalidArgumentException;

final class ImportContractCommand extends Command
{
    protected $signature = 'import:contract {contract : Data transfer contract key} {file : File name within the imports directory} {--format= : Explicit format override}';

    protected $description = 'Import data for a configured contract from storage/app/imports.';

    public function handle(DataTransferManager $manager): int
    {
        $contractKey = Str::lower((string) $this->argument('contract'));
        $contract = $manager->resolve($contractKey);

        $file = (string) $this->argument('file');
        $this->assertValidFileName($file);

        $format = $this->option('format');
        $format = is_string($format) && $format !== ''
            ? Str::lower($format)
            : Str::lower(pathinfo($file, PATHINFO_EXTENSION));

        if ($format === '') {
            throw new InvalidArgumentException('Unable to determine the file format. Provide a --format option.');
        }

        if (! in_array($format, $contract->supportedFormats(), true)) {
            throw new InvalidArgumentException("Unsupported format [{$format}] for contract [{$contractKey}].");
        }

        $diskName = (string) config('data-transfer.disk', 'local');
        $disk = Storage::disk($diskName);
        if (! $disk instanceof FilesystemAdapter) {
            throw new InvalidArgumentException("Configured disk [{$diskName}] is not a local filesystem adapter.");
        }

        $importsPath = trim((string) config('data-transfer.imports_path', 'imports'), '/');
        if ($importsPath === '') {
            throw new InvalidArgumentException('The imports path configuration cannot be empty.');
        }

        if (! $disk->exists($importsPath)) {
            $disk->makeDirectory($importsPath);
        }

        $relativePath = $importsPath . '/' . $file;
        if (! $disk->exists($relativePath)) {
            throw new InvalidArgumentException("Import file [{$relativePath}] does not exist on disk [{$diskName}].");
        }

        $contract->import($format, $disk, $relativePath);

        $absolutePath = $disk->path($relativePath);

        Log::channel((string) config('data-transfer.log_channel', 'maintenance'))
            ->info('Data contract import completed.', [
                'contract' => $contractKey,
                'format'   => $format,
                'path'     => $absolutePath,
            ]);

        $this->components->info("Import completed from: {$absolutePath}");

        return self::SUCCESS;
    }

    private function assertValidFileName(string $filename): void
    {
        if (! preg_match('/^[A-Za-z0-9._-]+$/', $filename)) {
            throw new InvalidArgumentException('The file name may only contain letters, numbers, dashes, underscores, and dots.');
        }
    }
}
